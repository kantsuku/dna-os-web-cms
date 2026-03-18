@extends('layouts.app')

@section('title', $site->name . ' - 設定')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('sites.show', $site) }}" class="text-sm text-gray-500 hover:underline">&larr; {{ $site->name }}</a>
    <h1 class="text-2xl font-bold mt-1 mb-6">サイト設定</h1>

    <form method="POST" action="{{ route('sites.update', $site) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Clinic ID (DNA-OS)</label>
            <input type="text" name="clinic_id" value="{{ old('clinic_id', $site->clinic_id) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">医院名</label>
            <input type="text" name="name" value="{{ old('name', $site->name) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ドメイン</label>
            <input type="text" name="domain" value="{{ old('domain', $site->domain) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="active" {{ $site->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="maintenance" {{ $site->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="archived" {{ $site->status === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>

        <hr class="my-4">
        <h3 class="text-sm font-semibold text-gray-600">XServer接続情報</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">FTPホスト</label>
                <input type="text" name="xserver_host" value="{{ old('xserver_host', $site->xserver_host) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">FTPユーザー</label>
                <input type="text" name="xserver_ftp_user" value="{{ old('xserver_ftp_user', $site->xserver_ftp_user) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">FTPパスワード（変更する場合のみ入力）</label>
            <input type="password" name="xserver_ftp_pass"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">デプロイパス</label>
            <input type="text" name="xserver_deploy_path" value="{{ old('xserver_deploy_path', $site->xserver_deploy_path) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">テンプレートセット</label>
            <select name="template_set" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="default" {{ $site->template_set === 'default' ? 'selected' : '' }}>default</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">WPサイトURL（移行期）</label>
            <input type="url" name="wp_site_url" value="{{ old('wp_site_url', $site->wp_site_url) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('sites.show', $site) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">キャンセル</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">保存</button>
        </div>
    </form>
</div>
@endsection
