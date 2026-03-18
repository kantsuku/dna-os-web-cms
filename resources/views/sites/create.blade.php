@extends('layouts.app')

@section('title', '新規サイト作成')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">新規サイト作成</h1>

    <form method="POST" action="{{ route('sites.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Clinic ID (DNA-OS)</label>
            <input type="text" name="clinic_id" value="{{ old('clinic_id') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            @error('clinic_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">医院名</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ドメイン</label>
            <input type="text" name="domain" value="{{ old('domain') }}" placeholder="example-dental.com"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <hr class="my-4">
        <h3 class="text-sm font-semibold text-gray-600">XServer接続情報</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">FTPホスト</label>
                <input type="text" name="xserver_host" value="{{ old('xserver_host') }}" placeholder="sv1234.xserver.jp"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">FTPユーザー</label>
                <input type="text" name="xserver_ftp_user" value="{{ old('xserver_ftp_user') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">FTPパスワード</label>
            <input type="password" name="xserver_ftp_pass"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">デプロイパス</label>
            <input type="text" name="xserver_deploy_path" value="{{ old('xserver_deploy_path', '/public_html') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">テンプレートセット</label>
            <select name="template_set" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="default">default</option>
            </select>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <a href="{{ route('sites.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">キャンセル</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">作成</button>
        </div>
    </form>
</div>
@endsection
