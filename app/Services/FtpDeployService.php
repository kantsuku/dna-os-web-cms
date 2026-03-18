<?php

namespace App\Services;

use App\Models\PublishRecord;
use App\Models\Site;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;

class FtpDeployService
{
    /**
     * ビルド成果物をXServerにFTPデプロイ
     */
    public function deploy(Site $site, string $buildPath, PublishRecord $record): bool
    {
        $record->update(['deploy_status' => 'deploying']);

        try {
            $ftp = $this->createFtpFilesystem($site);
            $deployPath = rtrim($site->xserver_deploy_path, '/');

            // ビルド成果物を再帰的にアップロード
            $files = File::allFiles($buildPath);

            foreach ($files as $file) {
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());
                $remotePath = $deployPath . '/' . $relativePath;

                $ftp->write($remotePath, $file->getContents());
            }

            // .htaccessもアップロード
            $htaccessPath = $buildPath . '/.htaccess';
            if (File::exists($htaccessPath)) {
                $ftp->write($deployPath . '/.htaccess', File::get($htaccessPath));
            }

            $record->update([
                'deploy_status' => 'success',
                'deployed_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('FTPデプロイエラー', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);

            $record->update([
                'deploy_status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * ロールバック: 指定スナップショットを再デプロイ
     */
    public function rollback(Site $site, PublishRecord $targetRecord): PublishRecord
    {
        $snapshotPath = $targetRecord->snapshot_path;

        if (!$snapshotPath || !File::isDirectory($snapshotPath)) {
            throw new \RuntimeException('スナップショットが見つかりません: ' . $snapshotPath);
        }

        $rollbackRecord = PublishRecord::create([
            'site_id' => $site->id,
            'pages_json' => $targetRecord->pages_json,
            'snapshot_path' => $snapshotPath,
            'deploy_status' => 'pending',
            'deployed_by' => auth()->id(),
            'rollback_of' => $targetRecord->id,
        ]);

        $this->deploy($site, $snapshotPath, $rollbackRecord);

        return $rollbackRecord;
    }

    /**
     * FTP接続テスト
     */
    public function testConnection(Site $site): bool
    {
        try {
            $ftp = $this->createFtpFilesystem($site);
            $ftp->listContents($site->xserver_deploy_path)->toArray();
            return true;
        } catch (\Throwable $e) {
            Log::warning('FTP接続テスト失敗', [
                'site_id' => $site->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function createFtpFilesystem(Site $site): Filesystem
    {
        $options = FtpConnectionOptions::fromArray([
            'host' => $site->xserver_host,
            'username' => $site->xserver_ftp_user,
            'password' => $site->xserver_ftp_pass,
            'port' => 21,
            'ssl' => true,
            'passive' => true,
            'timeout' => 30,
        ]);

        return new Filesystem(new FtpAdapter($options));
    }
}
