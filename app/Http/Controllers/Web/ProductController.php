<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['branch', 'warehouse', 'shelf']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($branchId = $request->query('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'passive') {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('product_name')->paginate(20)->withQueryString();

        // Filtre dropdown'ı için — BelongsToCompany trait'i sayesinde
        // otomatik olarak yalnızca giriş yapan admin'in şirketinin şubeleri.
        $branches = Branch::orderBy('name')->get();

        return view('products.index', compact('products', 'branches'));
    }
}
