<?php

namespace App\Services;

use App\Models\DeployRecord;
use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;

class FtpDeployService
{
    public function deploy(Site $site, string $buildPath, DeployRecord $record): bool
    {
        $record->update(['deploy_status' => 'deploying']);

        try {
            $ftp = $this->createFtp($site);
            $deployPath = rtrim($site->xserver_deploy_path, '/');

            foreach (File::allFiles($buildPath) as $file) {
                $remotePath = $deployPath . '/' . str_replace('\\', '/', $file->getRelativePathname());
                $ftp->write($remotePath, $file->getContents());
            }

            $htaccess = $buildPath . '/.htaccess';
            if (File::exists($htaccess)) {
                $ftp->write($deployPath . '/.htaccess', File::get($htaccess));
            }

            $record->update(['deploy_status' => 'success', 'deployed_at' => now()]);
            return true;
        } catch (\Throwable $e) {
            Log::error('FTPデプロイエラー', ['site_id' => $site->id, 'error' => $e->getMessage()]);
            $record->update(['deploy_status' => 'failed', 'error_log' => $e->getMessage()]);
            return false;
        }
    }

    public function rollback(Site $site, DeployRecord $targetRecord): DeployRecord
    {
        if (!$targetRecord->build_path || !File::isDirectory($targetRecord->build_path)) {
            throw new \RuntimeException('スナップショットが見つかりません');
        }

        $rollbackRecord = DeployRecord::create([
            'site_id' => $site->id,
            'generation_snapshot' => $targetRecord->generation_snapshot,
            'build_path' => $targetRecord->build_path,
            'deploy_status' => 'pending',
            'deployed_by' => auth()->id(),
            'rollback_of' => $targetRecord->id,
        ]);

        $this->deploy($site, $targetRecord->build_path, $rollbackRecord);
        return $rollbackRecord;
    }

    public function testConnection(Site $site): bool
    {
        try {
            $ftp = $this->createFtp($site);
            $ftp->listContents($site->xserver_deploy_path)->toArray();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function createFtp(Site $site): Filesystem
    {
        return new Filesystem(new FtpAdapter(FtpConnectionOptions::fromArray([
            'host' => $site->xserver_host,
            'username' => $site->xserver_ftp_user,
            'password' => $site->xserver_ftp_pass,
            'port' => 21,
            'ssl' => true,
            'passive' => true,
            'timeout' => 30,
        ])));
    }
}
