@extends('layouts.app')

@section('title', 'Kullanıcılar')
@section('page_title', 'Kullanıcılar')

@section('content')

    <div class="flex justify-between items-center mb-4">
        <div></div>
        <a href="{{ route('users.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
            Yeni Kullanıcı
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Ad</th>
                    <th class="px-4 py-2 font-medium">Kullanıcı Adı</th>
                    <th class="px-4 py-2 font-medium">E-posta</th>
                    <th class="px-4 py-2 font-medium">Rol</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-slate-400">(sen)</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono">{{ $user->username }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $user->email ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if ($user->role === \App\Enums\UserRole::CompanyAdmin)
                                <span class="inline-block rounded-full bg-indigo-100 text-indigo-700 px-2 py-0.5 text-xs">Şirket Admini</span>
                            @else
                                <span class="inline-block rounded-full bg-sky-100 text-sky-700 px-2 py-0.5 text-xs">Sayım Personeli</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if ($user->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="text-sm text-slate-600 hover:underline mr-3">Düzenle</a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-slate-600 hover:underline">
                                        {{ $user->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">Henüz kullanıcı oluşturulmamış.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

@endsection
