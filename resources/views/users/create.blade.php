@extends('layouts.app')

@section('title', 'Yeni Kullanıcı')
@section('page_title', 'Yeni Kullanıcı')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Ad Soyad</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Kullanıcı Adı</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">E-posta (opsiyonel)</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Rol</label>
                <select name="role" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                    <option value="count_staff" @selected(old('role') === 'count_staff')>Sayım Personeli</option>
                    <option value="company_admin" @selected(old('role') === 'company_admin')>Şirket Admini</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Şifre</label>
                <input type="password" name="password" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Şifre (Tekrar)</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Oluştur
                </button>
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

@endsection
