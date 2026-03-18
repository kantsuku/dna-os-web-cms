@extends('layouts.app')
@section('title', 'サイト作成')
@section('content')
<div class="mb-6">
    <a href="{{ route('clinic.sites.index', $clinic) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; サイト一覧に戻る</a>
</div>
<h1 class="text-2xl font-bold mb-6">新規サイト作成</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('clinic.sites.store', $clinic) }}">
        @csrf

        <div class="space-y-5">
            {{-- clinic_id --}}
            <div>
                <label for="clinic_id" class="block text-sm font-medium text-gray-700 mb-1">医院ID</label>
                <input type="text" name="clinic_id" id="clinic_id" value="{{ old('clinic_id') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('clinic_id') border-red-500 @enderror">
                @error('clinic_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">サイト名</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- domain --}}
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">ドメイン</label>
                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" placeholder="example.com"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('domain') border-red-500 @enderror">
                @error('domain')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Xserver接続情報 --}}
            <fieldset class="border border-gray-200 rounded-md p-4">
                <legend class="text-sm font-medium text-gray-700 px-2">Xserver接続情報</legend>
                <div class="space-y-4 mt-2">
                    <div>
                        <label for="xserver_host" class="block text-sm text-gray-600 mb-1">ホスト</label>
                        <input type="text" name="xserver_host" id="xserver_host" value="{{ old('xserver_host') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_host') border-red-500 @enderror">
                        @error('xserver_host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_ftp_user" class="block text-sm text-gray-600 mb-1">FTPユーザー</label>
                        <input type="text" name="xserver_ftp_user" id="xserver_ftp_user" value="{{ old('xserver_ftp_user') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_ftp_user') border-red-500 @enderror">
                        @error('xserver_ftp_user')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_ftp_pass" class="block text-sm text-gray-600 mb-1">FTPパスワード</label>
                        <input type="password" name="xserver_ftp_pass" id="xserver_ftp_pass"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_ftp_pass') border-red-500 @enderror">
                        @error('xserver_ftp_pass')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="xserver_deploy_path" class="block text-sm text-gray-600 mb-1">デプロイパス</label>
                        <input type="text" name="xserver_deploy_path" id="xserver_deploy_path" value="{{ old('xserver_deploy_path') }}" placeholder="/home/user/example.com/public_html"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('xserver_deploy_path') border-red-500 @enderror">
                        @error('xserver_deploy_path')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            {{-- GAS Generator URL --}}
            <div>
                <label for="gas_generator_url" class="block text-sm font-medium text-gray-700 mb-1">GAS Generator URL</label>
                <input type="url" name="gas_generator_url" id="gas_generator_url" value="{{ old('gas_generator_url') }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('gas_generator_url') border-red-500 @enderror">
                @error('gas_generator_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md text-sm hover:bg-indigo-700">作成</button>
            <a href="{{ route('clinic.sites.index', $clinic) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md text-sm hover:bg-gray-300">キャンセル</a>
        </div>
    </form>
</div>
@endsection
