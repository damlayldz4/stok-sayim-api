<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Branch $branch)
    {
        $warehouses = $branch->warehouses()->withCount('shelves')->orderBy('name')->get();

        return view('warehouses.index', compact('branch', 'warehouses'));
    }

    public function create(Branch $branch)
    {
        return view('warehouses.create', compact('branch'));
    }

    public function store(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'warehouse_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($branch->warehouses()->where('warehouse_code', $data['warehouse_code'])->exists()) {
            return back()->withErrors(['warehouse_code' => 'Bu depo ID bu şubede zaten kullanılıyor.'])->withInput();
        }

        $branch->warehouses()->create($data);

        return redirect()->route('warehouses.index', $branch)->with('status', 'Depo oluşturuldu.');
    }

    public function edit(Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        return view('warehouses.edit', compact('branch', 'warehouse'));
    }

    public function update(Request $request, Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $warehouse->update($data);

        return redirect()->route('warehouses.index', $branch)->with('status', 'Depo güncellendi.');
    }

    /** Doküman kuralı: depo silinmez, aktif/pasif arasında geçiş yapılır. */
    public function toggle(Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $warehouse->update(['is_active' => ! $warehouse->is_active]);

        return back()->with('status', $warehouse->is_active ? 'Depo aktif yapıldı.' : 'Depo pasif yapıldı.');
    }

    /**
     * Warehouse'da doğrudan company_id olmadığı için (bkz. Model katmanı
     * README'si), $branch zaten BelongsToCompany ile şirkete göre
     * süzüldükten sonra, $warehouse'un gerçekten o $branch'a ait olup
     * olmadığını burada kontrol ediyoruz — API katmanındaki desenin aynısı.
     */
    private function ensureBelongsToBranch(Branch $branch, Warehouse $warehouse): void
    {
        abort_if($warehouse->branch_id !== $branch->id, 404);
    }
}
