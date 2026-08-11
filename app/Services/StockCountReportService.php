<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StockCountReportService
{
    /** Doküman md.15'teki seçilebilir rapor kolonları */
    public const COLUMNS = [
        'branch_name' => 'Şube Adı',
        'warehouse_name' => 'Depo Adı',
        'shelf_code' => 'Raf ID',
        'product_name' => 'Ürün Adı',
        'product_code' => 'Ürün Kodu',
        'barcode' => 'Barkod',
        'expected_quantity' => 'Beklenen Miktar',
        'counted_quantity' => 'Sayılan Miktar',
        'difference' => 'Stok Farkı',
        'status' => 'Sonuç Durumu',
        'counted_by_name' => 'Sayımı Yapan Kullanıcı',
        'counted_at' => 'Sayım Tarihi',
    ];

    private const STATUS_LABELS = [
        StockCountItem::STATUS_MATCHED => 'Eşleşiyor',
        StockCountItem::STATUS_SURPLUS => 'Fazla',
        StockCountItem::STATUS_SHORTAGE => 'Eksik',
        'sayilmamis' => 'Sayılmamış',
    ];

    /**
     * Sayımın kapsadığı tüm ürünleri (okutulmuş olsun ya da olmasın) tek
     * bir satır listesine dönüştürür.
     *
     * ÖNEMLİ: Artık aynı ürün birden fazla kez okutulduğunda her okutma
     * `stock_count_items`'ta AYRI bir satır (bkz. StockCountItemController::scan).
     * Bu yüzden burada bir ürünün TÜM okutma satırlarını topluyoruz
     * (danışman notu: "toplama yalnızca raporlarda olmalı") — ham veri
     * hep ayrı satırlar olarak kalıyor, sadece bu rapor görünümü topluyor.
     *
     * "Sayılmamış ürün" satırları, sayımın ürün kapsamında olup hiç
     * okutma satırı bulunmayan ürünlerden üretilir; bu durumda sayılan
     * miktar 0 kabul edilir (danışman notu: "ürün sayılmadıysa 0 stok").
     */
    public function buildRows(StockCount $stockCount, array $filters): Collection
    {
        $stockCount->loadMissing(['branch', 'warehouse', 'shelves']);

        $productsQuery = Product::query()
            ->where('branch_id', $stockCount->branch_id)
            ->where('warehouse_id', $stockCount->warehouse_id)
            ->where('is_active', true)
            ->with('shelf');

        // Sayım belirli raflarla sınırlandırılmışsa, ürünleri de o raflarla sınırla.
        if ($stockCount->shelves->isNotEmpty()) {
            $productsQuery->whereIn('shelf_id', $stockCount->shelves->pluck('id'));
        }

        if (! empty($filters['shelf_id'])) {
            $productsQuery->where('shelf_id', $filters['shelf_id']);
        }

        if (! empty($filters['product_id'])) {
            $productsQuery->where('id', $filters['product_id']);
        }

        if (! empty($filters['barcode'])) {
            $productsQuery->where('barcode', 'like', '%'.$filters['barcode'].'%');
        }

        $products = $productsQuery->get();

        // Ürün başına birden fazla okutma satırı olabileceği için gruplayarak topluyoruz.
        $itemsByProduct = $stockCount->items()->with('countedBy')->orderBy('id')->get()->groupBy('product_id');

        $branchName = $stockCount->branch->name;
        $warehouseName = $stockCount->warehouse->name;

        $rows = $products->map(function (Product $product) use ($itemsByProduct, $branchName, $warehouseName) {
            /** @var Collection<int, StockCountItem>|null $scans */
            $scans = $itemsByProduct->get($product->id);

            $hasScans = $scans && $scans->isNotEmpty();

            $expectedQuantity = $hasScans ? $scans->first()->expected_quantity : $product->expected_quantity;
            $countedQuantity = $hasScans ? $scans->sum('counted_quantity') : 0;
            $difference = $countedQuantity - $expectedQuantity;

            $statusCode = match (true) {
                ! $hasScans => 'sayilmamis',
                $difference > 0 => StockCountItem::STATUS_SURPLUS,
                $difference < 0 => StockCountItem::STATUS_SHORTAGE,
                default => StockCountItem::STATUS_MATCHED,
            };

            $lastScan = $hasScans ? $scans->sortByDesc('counted_at')->first() : null;
            $countedByNames = $hasScans
                ? $scans->pluck('countedBy.name')->filter()->unique()->implode(', ')
                : null;

            return [
                'branch_name' => $branchName,
                'warehouse_name' => $warehouseName,
                'shelf_code' => $product->shelf?->shelf_code,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'barcode' => $product->barcode,
                'expected_quantity' => $expectedQuantity,
                'counted_quantity' => $countedQuantity,
                'difference' => $difference,
                'status' => self::STATUS_LABELS[$statusCode],
                'counted_by_name' => $countedByNames,
                'counted_at' => $lastScan?->counted_at?->format('Y-m-d H:i'),
                '_status_code' => $statusCode,
                '_counted_by_ids' => $hasScans ? $scans->pluck('counted_by')->filter()->unique()->all() : [],
                '_counted_at_raw' => $lastScan?->counted_at,
            ];
        });

        return $this->applyResultFilters($rows, $filters);
    }

    private function applyResultFilters(Collection $rows, array $filters): Collection
    {
        if (! empty($filters['counted_by'])) {
            $rows = $rows->filter(fn ($r) => in_array((int) $filters['counted_by'], $r['_counted_by_ids'], true));
        }

        if (! empty($filters['result'])) {
            $wanted = collect($filters['result']);
            $rows = $rows->filter(fn ($r) => $wanted->contains($r['_status_code']));
        }

        if (! empty($filters['date_from'])) {
            $from = Carbon::parse($filters['date_from'])->startOfDay();
            $rows = $rows->filter(fn ($r) => $r['_counted_at_raw'] && $r['_counted_at_raw']->gte($from));
        }

        if (! empty($filters['date_to'])) {
            $to = Carbon::parse($filters['date_to'])->endOfDay();
            $rows = $rows->filter(fn ($r) => $r['_counted_at_raw'] && $r['_counted_at_raw']->lte($to));
        }

        return $rows
            ->map(fn ($r) => collect($r)->except(['_status_code', '_counted_by_ids', '_counted_at_raw'])->all())
            ->values();
    }

    /** Seçilen kolonlara göre satırları daraltır ve başlıkları üretir. */
    public function projectColumns(Collection $rows, array $selectedColumns): array
    {
        $columns = ! empty($selectedColumns) ? array_intersect($selectedColumns, array_keys(self::COLUMNS)) : array_keys(self::COLUMNS);
        $columns = array_values($columns);

        $data = $rows->map(function ($row) use ($columns) {
            $out = [];
            foreach ($columns as $col) {
                $out[$col] = $row[$col] ?? null;
            }

            return $out;
        })->all();

        $headings = array_map(fn ($col) => self::COLUMNS[$col], $columns);

        return ['data' => $data, 'headings' => $headings];
    }
}