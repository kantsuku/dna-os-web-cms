<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\MediaFile;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Clinic $clinic, Request $request)
    {
        $folderId = $request->input('folder');
        $folders = MediaFolder::where('clinic_id', $clinic->id)
            ->where('parent_id', $folderId)
            ->orderBy('sort_order')
            ->get();

        $files = MediaFile::where('clinic_id', $clinic->id)
            ->where('folder_id', $folderId)
            ->orderByDesc('created_at')
            ->paginate(24);

        $currentFolder = $folderId ? MediaFolder::find($folderId) : null;
        $breadcrumb = $this->buildBreadcrumb($currentFolder);

        return view('media.index', compact('clinic', 'folders', 'files', 'currentFolder', 'breadcrumb'));
    }

    public function upload(Request $request, Clinic $clinic)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $folderId = $request->input('folder_id');
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "media/{$clinic->clinic_id}/" . now()->format('Y/m') . "/{$filename}";

            Storage::disk('public')->putFileAs(
                dirname($path), $file, basename($path)
            );

            $dimensions = @getimagesize($file->getRealPath());

            $media = MediaFile::create([
                'clinic_id' => $clinic->id,
                'folder_id' => $folderId,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'disk' => 'public',
                'path' => $path,
                'uploaded_by' => auth()->id(),
            ]);

            $uploaded[] = $media;
        }

        return redirect()->back()->with('success', count($uploaded) . '件のファイルをアップロードしました');
    }

    public function createFolder(Request $request, Clinic $clinic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        MediaFolder::create([
            'clinic_id' => $clinic->id,
            'parent_id' => $request->input('parent_id'),
            'name' => $request->input('name'),
        ]);

        return redirect()->back()->with('success', 'フォルダを作成しました');
    }

    public function destroy(Clinic $clinic, MediaFile $media)
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        return redirect()->back()->with('success', 'ファイルを削除しました');
    }

    public function updateAlt(Request $request, Clinic $clinic, MediaFile $media)
    {
        $media->update(['alt_text' => $request->input('alt_text')]);
        return response()->json(['ok' => true]);
    }

    /**
     * 画像選択ピッカー（モーダル用、JSONで返す）
     */
    public function picker(Clinic $clinic, Request $request)
    {
        $folderId = $request->input('folder');
        $folders = MediaFolder::where('clinic_id', $clinic->id)
            ->where('parent_id', $folderId)->orderBy('name')->get();
        $files = MediaFile::where('clinic_id', $clinic->id)
            ->where('folder_id', $folderId)
            ->where('mime_type', 'like', 'image/%')
            ->orderByDesc('created_at')->limit(50)->get();

        if ($request->expectsJson()) {
            return response()->json([
                'folders' => $folders->map(fn($f) => ['id' => $f->id, 'name' => $f->name]),
                'files' => $files->map(fn($f) => [
                    'id' => $f->id, 'url' => $f->url, 'alt' => $f->alt_text,
                    'name' => $f->original_name, 'size' => $f->human_size,
                ]),
            ]);
        }

        return view('media.picker', compact('clinic', 'folders', 'files', 'folderId'));
    }

    private function buildBreadcrumb(?MediaFolder $folder): array
    {
        $crumbs = [];
        while ($folder) {
            array_unshift($crumbs, $folder);
            $folder = $folder->parent;
        }
        return $crumbs;
    }
}
