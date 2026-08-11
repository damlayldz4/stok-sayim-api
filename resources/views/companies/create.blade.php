@extends('layouts.app')

@section('title', 'Yeni Şirket')
@section('page_title', 'Yeni Şirket')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('companies.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Şirket Adı</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="pt-2 border-t">
                <p class="text-sm font-medium text-slate-700 mb-3">İlk Admin Hesabı</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Admin Ad Soyad</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Admin Kullanıcı Adı</label>
                <input type="text" name="admin_username" value="{{ old('admin_username') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Admin E-posta (opsiyonel)</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Admin Şifresi</label>
                <input type="password" name="admin_password" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Admin Şifresi (Tekrar)</label>
                <input type="password" name="admin_password_confirmation" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Şirketi Oluştur
                </button>
                <a href="{{ route('companies.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

@endsection
