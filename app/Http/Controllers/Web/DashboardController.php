<?php

namespace App\Http\Controllers\Web;

use App\Enums\StockCountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === UserRole::SystemAdmin) {
            return view('dashboard', [
                'view' => 'system_admin',
                'stats' => [
                    'company_count' => Company::count(),
                    'active_company_count' => Company::where('is_active', true)->count(),
                ],
            ]);
        }

        if ($user->role === UserRole::CompanyAdmin) {
            return view('dashboard', [
                'view' => 'company_admin',
                'stats' => [
                    'branch_count' => Branch::count(),
                    'warehouse_count' => Warehouse::whereHas('branch')->count(),
                    'product_count' => Product::where('is_active', true)->count(),
                    'active_stock_count' => StockCount::where('status', StockCountStatus::InProgress)->count(),
                    'recent_stock_counts' => StockCount::with('branch', 'warehouse')->latest()->take(5)->get(),
                ],
            ]);
        }

        // count_staff — asıl işini mobil uygulamada yapıyor, web panelinde
        // sadece yönlendirici bir ekran görüyor.
        return view('dashboard', ['view' => 'count_staff', 'stats' => []]);
    }
}
