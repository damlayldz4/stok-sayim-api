<?php

namespace App\Models;

use App\Enums\StockImportMode;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockImport extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'imported_by',
        'mode',
        'file_name',
        'total_rows',
        'success_rows',
        'error_rows',
        'error_details',
    ];

    protected $casts = [
        'mode' => StockImportMode::class,
        'error_details' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
    
     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
