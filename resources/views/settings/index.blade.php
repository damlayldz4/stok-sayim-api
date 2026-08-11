@extends('layouts.app')

@section('title', 'Ayarlar')
@section('page_title', 'Ayarlar')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <h2 class="text-sm font-medium mb-1">Barkod Okutma — Miktar Uyarı Eşiği</h2>
        <p class="text-xs text-slate-500 mb-4">
            Sayım personeli mobil uygulamada barkod okutup bu değerden daha
            büyük bir miktar girerse, kayıt eklenmeden önce "emin misin?"
            diye onay ister. Boş bırakırsan bu uyarı hiç gösterilmez.
        </p>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Eşik Değeri</label>
                <input type="number" name="scan_quantity_warning_threshold" min="1"
                       value="{{ old('scan_quantity_warning_threshold', $company->scan_quantity_warning_threshold) }}"
                       placeholder="örn. 50 (boş = uyarı yok)"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                Kaydet
            </button>
        </form>
    </div>

@endsection