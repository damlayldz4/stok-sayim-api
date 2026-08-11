<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Shelf;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index(Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        return $warehouse->shelves()->orderBy('shelf_code')->get();
    }

    public function store(Request $request, Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $data = $request->validate([
            'shelf_code' => 'required|string|max:50',
        ]);

        if ($warehouse->shelves()->where('shelf_code', $data['shelf_code'])->exists()) {
            return response()->json(['message' => 'Bu raf ID bu depoda zaten kullanılıyor.'], 422);
        }

        $shelf = $warehouse->shelves()->create($data);

        return response()->json($shelf, 201);
    }

    public function update(Request $request, Branch $branch, Warehouse $warehouse, Shelf $shelf)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);
        abort_if((int) $shelf->warehouse_id !== (int) $warehouse->id, 404);
        $data = $request->validate([
            'is_active' => 'sometimes|boolean',
        ]);

        $shelf->update($data);

        return $shelf;
    }

    /** Doküman kuralı: raf silinmez, pasif yapılır. */
    public function destroy(Branch $branch, Warehouse $warehouse, Shelf $shelf)
{
    $this->ensureBelongsToBranch($branch, $warehouse);

    abort_if(
        (int) $shelf->warehouse_id !== (int) $warehouse->id,
        404
    );

    $shelf->update(['is_active' => false]);

    return response()->json([
        'message' => 'Raf pasif duruma getirildi.'
    ]);
}

    private function ensureBelongsToBranch(Branch $branch, Warehouse $warehouse): void
{
    abort_if((int) $warehouse->branch_id !== (int) $branch->id, 404);
}
}
