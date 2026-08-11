<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    use HasFactory;

    public const STATUS_MATCHED = 'eslesen';
    public const STATUS_SURPLUS = 'fazla';
    public const STATUS_SHORTAGE = 'eksik';

    protected $fillable = [
        'stock_count_id',
        'product_id',
        'expected_quantity',
        'counted_quantity',
        'counted_by',
        'counted_at',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'counted_at' => 'datetime',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    /** Sayılan miktar - beklenen miktar */
    public function getDifferenceAttribute(): int
    {
        return $this->counted_quantity - $this->expected_quantity;
    }

    /** eslesen | fazla | eksik
     *  Not: "Sayılmamış ürün" durumu bu tabloda satır olmayan ürünler için
     *  raporlama katmanında (ürün listesi ile bu tablo arasındaki fark alınarak)
     *  belirlenir, burada temsil edilmez.
     */
    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->difference > 0 => self::STATUS_SURPLUS,
            $this->difference < 0 => self::STATUS_SHORTAGE,
            default => self::STATUS_MATCHED,
        };
    }
}
