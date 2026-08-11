<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /** Mobil uygulamanın barkod okuttuktan sonra çağırdığı endpoint. */
    public function findByBarcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if (! $product) {
            return response()->json([
                'message' => 'Bu barkoda ait ürün bulunamadı.',
            ], 404);
        }

        return $product;
    }

    public function show(Product $product)
    {
        return $product;
    }
}
