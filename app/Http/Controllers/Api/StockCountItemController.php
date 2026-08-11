<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockCountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Http\Request;

class StockCountItemController extends Controller
{
    /**
     * Sayımdaki (okutulmuş) ürünleri listeleme — girildiği sırayla
     * (danışman notu: sıra önemli).
     *
     * Ham Eloquent modelini değil, açık bir dizi döndürüyoruz: model
     * hem `counted_by` kolonuna (int, kullanıcı id'si) hem de
     * `countedBy()` ilişkisine sahip — ikisi de JSON'a çevrilirken aynı
     * `counted_by` anahtarına düşer ve ilişki kolonun üzerine yazar.
     * Mobil tarafın hangisini aldığını asla şaşırmaması için burada
     * alanları biz adlandırıyoruz.
     */
    public function index(Request $request, StockCount $stockCount)
    {
        $this->ensureAssigned($request, $stockCount);

        $items = $stockCount->items()->with(['product', 'countedBy'])->orderBy('id')->get();

        return $items->map(fn (StockCountItem $item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product' => [
                'id' => $item->product->id,
                'product_name' => $item->product->product_name,
                'product_code' => $item->product->product_code,
                'barcode' => $item->product->barcode,
                'expected_quantity' => $item->product->expected_quantity,
            ],
            'expected_quantity' => $item->expected_quantity,
            'counted_quantity' => $item->counted_quantity,
            'difference' => $item->difference,
            'status' => $item->status,
            'counted_by_name' => $item->countedBy?->name,
            'counted_at' => $item->counted_at?->toIso8601String(),
        ]);
    }

    /**
     * Mobil uygulama barkod okuttuğunda çağrılır.
     *
     * Danışman notu üzerine: aynı ürün tekrar okutulsa bile HER okutma
     * ayrı bir satır olarak kaydedilir (sıra takibi için) — mevcut
     * satırın miktarını artırmıyoruz. Toplama yalnızca raporlama
     * katmanında yapılır (bkz. StockCountReportService).
     *
     * Girilen miktar şirketin belirlediği eşiği aşarsa, `confirm: true`
     * gönderilmeden kayıt oluşturulmaz — mobil önce kullanıcıya onay sorar.
     */
    public function scan(Request $request, StockCount $stockCount)
    {
        $this->ensureAssigned($request, $stockCount);

        abort_if(
            $stockCount->status !== StockCountStatus::InProgress,
            422,
            'Sayım devam etmiyor, kayıt eklenemez.'
        );

        $data = $request->validate([
            'barcode' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
            'confirm' => 'sometimes|boolean',
        ]);

        $product = Product::where('barcode', $data['barcode'])->first();

        if (! $product) {
            return response()->json(['message' => 'Bu barkoda ait ürün bulunamadı.'], 404);
        }

        $quantity = $data['quantity'] ?? 1;
        $confirm = (bool) ($data['confirm'] ?? false);

        $threshold = $stockCount->company->scan_quantity_warning_threshold;

        if ($threshold !== null && $quantity > $threshold && ! $confirm) {
            return response()->json([
                'status' => 'confirmation_required',
                'message' => "Girdiğin miktar ({$quantity}), şirketin belirlediği {$threshold} eşiğinin üzerinde. Devam etmek istiyor musun?",
                'threshold' => $threshold,
                'quantity' => $quantity,
            ], 409);
        }

        $item = $stockCount->items()->create([
            'product_id' => $product->id,
            'expected_quantity' => $product->expected_quantity,
            'counted_quantity' => $quantity,
            'counted_by' => $request->user()->id,
            'counted_at' => now(),
        ]);

        return response()->json($item->load('product'), 201);
    }

    /** Kullanıcı sayım tamamlanmadan önce, TEK bir okutma kaydının miktarını manuel değiştirebilir. */
    public function update(Request $request, StockCount $stockCount, StockCountItem $item)
    {
        $this->ensureAssigned($request, $stockCount);
        abort_if($item->stock_count_id !== $stockCount->id, 404);

        abort_if(
            $stockCount->status !== StockCountStatus::InProgress,
            422,
            'Sayım devam etmiyor, kayıt değiştirilemez.'
        );

        $data = $request->validate([
            'counted_quantity' => 'required|integer|min:0',
            'confirm' => 'sometimes|boolean',
        ]);

        $threshold = $stockCount->company->scan_quantity_warning_threshold;
        $confirm = (bool) ($data['confirm'] ?? false);

        if ($threshold !== null && $data['counted_quantity'] > $threshold && ! $confirm) {
            return response()->json([
                'status' => 'confirmation_required',
                'message' => "Girdiğin miktar ({$data['counted_quantity']}), şirketin belirlediği {$threshold} eşiğinin üzerinde. Devam etmek istiyor musun?",
                'threshold' => $threshold,
                'quantity' => $data['counted_quantity'],
            ], 409);
        }

        $item->update([
            'counted_quantity' => $data['counted_quantity'],
            'counted_by' => $request->user()->id,
            'counted_at' => now(),
        ]);

        return $item->load('product');
    }

    private function ensureAssigned(Request $request, StockCount $stockCount): void
    {
        $user = $request->user();

        if ($user->role !== UserRole::CountStaff) {
            return;
        }

        $isAssigned = $stockCount->assignedUsers()->where('users.id', $user->id)->exists();

        abort_if(! $isAssigned, 403, 'Bu sayıma erişim yetkiniz yok.');
    }
}