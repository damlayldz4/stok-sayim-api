@extends('layouts.app')

@section('title', 'İçe Aktarım Onayı')
@section('page_title', 'CSV İçe Aktarım — Onay Gerekiyor')

@section('content')

    <div class="bg-white rounded-lg shadow p-6 max-w-xl">
        <div class="rounded bg-amber-50 text-amber-800 border border-amber-200 px-4 py-3 text-sm mb-4">
            {{ $result['message'] }}
        </div>

        <div class="text-sm text-slate-600 mb-4 space-y-1">
            <div>Toplam satır: {{ $result['total_rows'] }}</div>
            <div>Geçerli satır: {{ $result['success_rows'] }}</div>
            <div>Hatalı satır: {{ $result['error_rows'] }}</div>
            <div class="font-medium text-amber-800">Pasif yapılacak ürün sayısı: {{ $result['will_deactivate_count'] }}</div>
        </div>

        @if ($result['will_deactivate_count'] > 0)
            <div class="mb-4">
                <div class="text-sm font-medium mb-1">Pasif yapılacak barkodlar</div>
                <div class="text-xs text-slate-500 max-h-32 overflow-y-auto border rounded p-2 font-mono">
                    {{ implode(', ', $result['will_deactivate_barcodes']) }}
                </div>
            </div>
        @endif

        @if ($result['error_rows'] > 0)
            <div class="mb-4">
                <div class="text-sm font-medium mb-1">Hatalı satırlar (bunlar aktarılmayacak)</div>
                <ul class="list-disc list-inside text-xs text-slate-500 max-h-32 overflow-y-auto">
                    @foreach ($result['errors'] as $error)
                        <li>Satır {{ $error['line'] }}: {{ $error['reason'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex gap-3">
            <form method="POST" action="{{ route('imports.confirm') }}">
                @csrf
                <input type="hidden" name="temp_path" value="{{ $temp_path }}">
                <input type="hidden" name="original_name" value="{{ $original_name }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <button type="submit" class="bg-red-700 text-white rounded px-4 py-2 text-sm font-medium hover:bg-red-800">
                    Onayla ve {{ $result['will_deactivate_count'] }} Ürünü Pasif Yap
                </button>
            </form>

            <a href="{{ route('imports.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
        </div>
    </div>

@endsection
