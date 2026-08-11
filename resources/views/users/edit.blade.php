@extends('layouts.app')

@section('title', 'Kullanıcı Düzenle')
@section('page_title', 'Kullanıcı Düzenle')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg mb-6">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Kullanıcı Adı</label>
                <input type="text" value="{{ $user->username }}" disabled
                       class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Ad Soyad</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">E-posta</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Rol</label>
                <select name="role" class="w-full rounded border border-slate-300 px-3 py-2 text-sm"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <option value="count_staff" @selected(old('role', $user->role->value) === 'count_staff')>Sayım Personeli</option>
                    <option value="company_admin" @selected(old('role', $user->role->value) === 'company_admin')>Şirket Admini</option>
                </select>
                @if ($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role->value }}">
                    <p class="text-xs text-slate-500 mt-1">Kendi rolünü değiştiremezsin.</p>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Kaydet
                </button>
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <h2 class="text-sm font-medium mb-3">Şifre Sıfırla</h2>
        <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Yeni Şifre</label>
                <input type="password" name="password" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Yeni Şifre (Tekrar)</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <button type="submit" class="bg-slate-700 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                Şifreyi Güncelle
            </button>
        </form>
    </div>

@endsection
