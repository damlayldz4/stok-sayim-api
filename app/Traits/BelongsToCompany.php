<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * "Kullanıcı başka şirketin verilerine erişememelidir" kuralını
 * model seviyesinde otomatik uygular.
 *
 * company_id kolonu olan modellere eklenir (Branch, Product,
 * StockImport, StockCount...). Giriş yapmış kullanıcı sistem
 * yöneticisi değilse, sorgular otomatik olarak kendi company_id'siyle
 * sınırlandırılır.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || $user->role === UserRole::SystemAdmin) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.company_id', $user->company_id);
        });

        static::creating(function ($model) {
            $user = Auth::user();

            if ($user && ! $model->company_id && $user->company_id) {
                $model->company_id = $user->company_id;
            }
        });
    }
}
