<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Branch $branch)
    {
        return $branch->warehouses()->orderBy('name')->get();
    }

    public function store(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'warehouse_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($branch->warehouses()->where('warehouse_code', $data['warehouse_code'])->exists()) {
            return response()->json(['message' => 'Bu depo ID bu şubede zaten kullanılıyor.'], 422);
        }

        $warehouse = $branch->warehouses()->create($data);

        return response()->json($warehouse, 201);
    }

    public function show(Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        return $warehouse;
    }

    public function update(Request $request, Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $warehouse->update($data);

        return $warehouse;
    }

    /** Doküman kuralı: depo silinmez, pasif yapılır. */
    public function destroy(Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $warehouse->update(['is_active' => false]);

        return response()->json(['message' => 'Depo pasif duruma getirildi.']);
    }

    /**
     * Warehouse'da doğrudan company_id olmadığı için (bkz. Model katmanı
     * README'si), depo route'a gelen $branch'a ait mi diye burada kontrol
     * ediyoruz. $branch zaten BelongsToCompany ile şirkete göre filtrelenmiş
     * olduğundan, bu kontrol dolaylı olarak şirket izolasyonunu da sağlıyor.
     */
    private function ensureBelongsToBranch(Branch $branch, Warehouse $warehouse): void
    {
        abort_if($warehouse->branch_id !== $branch->id, 404);
    }
}
