@extends('layouts.app')
@section('title', $site->name . ' - 設定編集')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; {{ $site->name }}に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">サイト設定編集</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('clinic.sites.update', [$clinic, $site]) }}">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <div>
                <label for="clinic_id" class="block text-sm font-medium text-gray-700 mb-1">医院ID</label>
                <input type="text" name="clinic_id" id="clinic_id" value="{{ old('clinic_id', $site->clinic_id) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('clinic_id') border-red-500 @enderror">
                @error('clinic_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">サイト名</label>
                <input type="text" name="name" id="name" value="{{ old('name', $site->name) }}" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">ドメイン</label>
                <input type="text" name="domain" id="domain" value="{{ old('domain', $site->domain) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('domain') border-red-500 @enderror">
                @error('domain')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <fieldset class="border border-gray-200 rounded-md p-4">
                <legend class="text-sm font-medium text-gray-700 px-2">Xserver接続情報</legend>
                <div class="space-y-4 mt-2">
                    <div>
                        <label for="xserver_host" class="block text-sm text-gray-600 mb-1">ホスト</label>
                        <input type="text" name="xserver_host" id="xserver_host" value="{{ old('xserver_host', $site->xserver_host) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_host') border-red-500 @enderror">
                        @error('xserver_host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_ftp_user" class="block text-sm text-gray-600 mb-1">FTPユーザー</label>
                        <input type="text" name="xserver_ftp_user" id="xserver_ftp_user" value="{{ old('xserver_ftp_user', $site->xserver_ftp_user) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_ftp_user') border-red-500 @enderror">
                        @error('xserver_ftp_user')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_ftp_pass" class="block text-sm text-gray-600 mb-1">FTPパスワード</label>
                        <input type="password" name="xserver_ftp_pass" id="xserver_ftp_pass" placeholder="変更する場合のみ入力"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_ftp_pass') border-red-500 @enderror">
                        @error('xserver_ftp_pass')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_deploy_path" class="block text-sm text-gray-600 mb-1">デプロイパス</label>
                        <input type="text" name="xserver_deploy_path" id="xserver_deploy_path" value="{{ old('xserver_deploy_path', $site->xserver_deploy_path) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_deploy_path') border-red-500 @enderror">
                        @error('xserver_deploy_path')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            <div>
                <label for="gas_generator_url" class="block text-sm font-medium text-gray-700 mb-1">GAS Generator URL</label>
                <input type="url" name="gas_generator_url" id="gas_generator_url" value="{{ old('gas_generator_url', $site->gas_generator_url) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('gas_generator_url') border-red-500 @enderror">
                @error('gas_generator_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active" @selected(old('status', $site->status) === 'active')>active</option>
                    <option value="inactive" @selected(old('status', $site->status) === 'inactive')>inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex items-center space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">更新</button>
            <a href="{{ route('clinic.sites.show', [$clinic, $site]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
            @if($site->xserver_host)
            <form method="POST" action="{{ route('clinic.sites.test-ftp', [$clinic, $site]) }}" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-600">FTP接続テスト</button>
            </form>
            @endif
        </div>
    </form>
</div>
@endsection
