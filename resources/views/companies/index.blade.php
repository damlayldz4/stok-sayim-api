@extends('layouts.app')

@section('title', 'Şirketler')
@section('page_title', 'Şirketler')

@section('content')

    <div class="flex justify-between items-center mb-4">
        <div></div>
        <a href="{{ route('companies.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
            Yeni Şirket
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Şirket Adı</th>
                    <th class="px-4 py-2 font-medium text-right">Kullanıcı Sayısı</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $company->name }}</td>
                        <td class="px-4 py-2 text-right">{{ $company->users_count }}</td>
                        <td class="px-4 py-2">
                            @if ($company->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('companies.toggle', $company) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-slate-600 hover:underline">
                                    {{ $company->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">Henüz şirket oluşturulmamış.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>

@endsection
