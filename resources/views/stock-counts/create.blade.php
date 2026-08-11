@extends('layouts.app')

@section('title', 'Yeni Sayım')
@section('page_title', 'Yeni Sayım')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-2xl">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form method="POST" action="{{ route('stock-counts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Sayım Adı</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Şube</label>
                    <select id="branch_select" name="branch_id" required
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Seçiniz</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Depo</label>
                    <select id="warehouse_select" name="warehouse_id" required
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Önce şube seçin</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Raf(lar) <span class="text-slate-400 font-normal">(boş bırakılırsa tüm depo kapsanır)</span></label>
                <div id="shelf_checkboxes" class="border border-slate-300 rounded p-3 text-sm text-slate-500">
                    Önce depo seçin.
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Bitiş Tarihi</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Açıklama</label>
                <textarea name="description" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Sayım Personeli</label>
                @if ($staff->isEmpty())
                    <p class="text-sm text-slate-500">Bu şirkette henüz aktif sayım personeli yok —
                        <a href="{{ route('users.create') }}" class="underline">önce bir kullanıcı oluştur</a>.</p>
                @else
                    <div class="border border-slate-300 rounded p-3 space-y-1 max-h-40 overflow-y-auto">
                        @foreach ($staff as $person)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="user_ids[]" value="{{ $person->id }}"
                                       @checked(collect(old('user_ids'))->contains($person->id))>
                                {{ $person->name }} <span class="text-slate-400">({{ $person->username }})</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Sayımı Oluştur
                </button>
                <a href="{{ route('stock-counts.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

    @php
        // Blade'in @json() direktifi, içine doğrudan yazılan karmaşık iç içe
        // fn()=>[...] ifadelerinde parantez/köşeli parantez eşleşmesini
        // bazen yanlış çözüyor ("Unclosed '[' does not match ')'" hatası).
        // Bu yüzden veriyi önce düz bir PHP dizisine çeviriyoruz, @json()'a
        // sadece hazır diziyi veriyoruz.
        $branchData = $branches->keyBy('id')->map(function ($b) {
            return [
                'warehouses' => $b->warehouses->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'name' => $w->name,
                        'shelves' => $w->shelves->map(function ($s) {
                            return ['id' => $s->id, 'code' => $s->shelf_code];
                        })->values(),
                    ];
                })->values(),
            ];
        });
    @endphp

    <script>
        // Şube -> Depo -> Raf hiyerarşisini sunucudan tek seferde alıp
        // tarayıcıda filtreliyoruz (ekstra AJAX isteği yok).
        const branchData = @json($branchData);

        const branchSelect = document.getElementById('branch_select');
        const warehouseSelect = document.getElementById('warehouse_select');
        const shelfContainer = document.getElementById('shelf_checkboxes');

        function renderWarehouses(branchId) {
            warehouseSelect.innerHTML = '<option value="">Seçiniz</option>';
            shelfContainer.innerHTML = '<span class="text-slate-500">Önce depo seçin.</span>';

            const branch = branchData[branchId];
            if (! branch) return;

            branch.warehouses.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.textContent = w.name;
                warehouseSelect.appendChild(opt);
            });
        }

        function renderShelves(branchId, warehouseId) {
            const branch = branchData[branchId];
            if (! branch) return;

            const warehouse = branch.warehouses.find(w => String(w.id) === String(warehouseId));
            shelfContainer.innerHTML = '';

            if (! warehouse || warehouse.shelves.length === 0) {
                shelfContainer.innerHTML = '<span class="text-slate-500">Bu depoda raf tanımlı değil.</span>';
                return;
            }

            warehouse.shelves.forEach(s => {
                const label = document.createElement('label');
                label.className = 'flex items-center gap-2 text-sm';
                label.innerHTML = `<input type="checkbox" name="shelf_ids[]" value="${s.id}"> ${s.code}`;
                shelfContainer.appendChild(label);
            });
        }

        branchSelect.addEventListener('change', () => renderWarehouses(branchSelect.value));
        warehouseSelect.addEventListener('change', () => renderShelves(branchSelect.value, warehouseSelect.value));

        // Doğrulama hatası sonrası eski seçimleri geri yükle
        @if (old('branch_id'))
            renderWarehouses('{{ old('branch_id') }}');
            warehouseSelect.value = '{{ old('warehouse_id') }}';
            renderShelves('{{ old('branch_id') }}', '{{ old('warehouse_id') }}');
        @endif
    </script>

@endsection