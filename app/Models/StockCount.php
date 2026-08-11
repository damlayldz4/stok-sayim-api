<?php

namespace App\Models;

use App\Enums\StockCountStatus;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCount extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'branch_id',
        'warehouse_id',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => StockCountStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Sayıma dahil edilen raflar (boşsa tüm depo kastedilir) */
    public function shelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class, 'stock_count_shelves');
    }

    /** Sayımda görevlendirilen personel */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'stock_count_users')
            ->withPivot('assigned_at');
    }

    /** Okutulan ürün kayıtları */
    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === StockCountStatus::Completed;
    }
}
