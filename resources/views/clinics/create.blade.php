@extends('layouts.app')
@section('title', '新規医院登録')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">新規医院登録</h1>

    <form method="POST" action="{{ route('clinics.store') }}" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">医院ID（DNA-OS上のclinic_id）</label>
            <input type="text" name="clinic_id" required value="{{ old('clinic_id') }}" class="w-full border-gray-300 rounded text-sm" placeholder="例: KAMEARI_ORTHO">
            @error('clinic_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">医院名</label>
            <input type="text" name="name" required value="{{ old('name') }}" class="w-full border-gray-300 rounded text-sm" placeholder="例: 亀有矯正歯科">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">DNA-OS GAS WebApp URL</label>
            <input type="url" name="gas_webapp_url" value="{{ old('gas_webapp_url') }}" class="w-full border-gray-300 rounded text-sm" placeholder="https://script.google.com/macros/s/.../exec">
        </div>
        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded text-sm hover:bg-indigo-700">登録する</button>
            <a href="{{ route('clinics.select') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded text-sm hover:bg-gray-200">キャンセル</a>
        </div>
    </form>
</div>
@endsection
