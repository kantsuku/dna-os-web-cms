{{-- メディアピッカー（モーダル/iframe埋め込み用） --}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { margin: 0; font-family: 'Noto Sans JP', sans-serif; background: #f9fafb; }
.picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
.picker-item { cursor: pointer; border: 2px solid transparent; border-radius: 8px; overflow: hidden; transition: border-color 0.15s, box-shadow 0.15s; }
.picker-item:hover { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.2); }
.picker-item.selected { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.3); }
.picker-item img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }
.picker-item-name { font-size: 11px; padding: 4px 6px; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
</head>
<body>
<div id="picker-app" style="padding: 16px;">
    {{-- パンくず --}}
    <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px; font-size:13px;">
        <a href="#" onclick="navigateFolder(null); return false;"
           style="color:#6366f1; text-decoration:none;">ルート</a>
    </div>

    {{-- アップロード --}}
    <div style="margin-bottom: 12px;">
        <form method="POST" action="{{ route('clinic.media.upload', $clinic) }}" enctype="multipart/form-data" id="upload-form">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $folderId }}">
            <label style="display:inline-flex; align-items:center; gap:6px; background:#4f46e5; color:#fff; padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                + アップロード
                <input type="file" name="files[]" multiple accept="image/*" style="display:none"
                       onchange="this.closest('form').submit()">
            </label>
        </form>
    </div>

    {{-- フォルダ --}}
    @if($folders->isNotEmpty())
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
            @foreach($folders as $folder)
                <a href="#" onclick="navigateFolder({{ $folder->id }}); return false;"
                   style="display:flex; align-items:center; gap:4px; background:#fff; border:1px solid #e5e7eb; border-radius:6px; padding:6px 12px; font-size:12px; color:#374151; text-decoration:none;">
                    📁 {{ $folder->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- 画像グリッド --}}
    @if($files->isNotEmpty())
        <div class="picker-grid">
            @foreach($files as $file)
                <div class="picker-item"
                     data-url="{{ $file->url }}"
                     data-alt="{{ $file->alt_text ?? $file->original_name }}"
                     data-name="{{ $file->original_name }}"
                     onclick="selectImage(this)">
                    <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" loading="lazy">
                    <div class="picker-item-name">{{ $file->original_name }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center; padding:40px; color:#9ca3af; font-size:13px;">
            画像がありません
        </div>
    @endif

    {{-- 選択確定 --}}
    <div id="picker-actions" style="display:none; position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb; padding:12px; margin:12px -16px -16px; text-align:right;">
        <span id="selected-name" style="font-size:12px; color:#6b7280; margin-right:12px;"></span>
        <button onclick="confirmSelection()" style="background:#4f46e5; color:#fff; border:none; padding:8px 20px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">
            この画像を挿入
        </button>
    </div>
</div>

<script>
var selectedData = null;

function selectImage(el) {
    document.querySelectorAll('.picker-item').forEach(function(item) { item.classList.remove('selected'); });
    el.classList.add('selected');
    selectedData = { url: el.dataset.url, alt: el.dataset.alt, name: el.dataset.name };
    document.getElementById('selected-name').textContent = selectedData.name;
    document.getElementById('picker-actions').style.display = 'block';
}

function confirmSelection() {
    if (!selectedData) return;
    // 親ウィンドウにメッセージ送信
    window.parent.postMessage({ type: 'media-selected', ...selectedData }, '*');
}

function navigateFolder(folderId) {
    var url = @json(route('clinic.media.picker', $clinic)) + (folderId ? '?folder=' + folderId : '');
    window.location.href = url;
}
</script>
</body>
</html>
