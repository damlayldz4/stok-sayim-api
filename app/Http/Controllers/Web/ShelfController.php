<?php

namespace App\Http\Controllers\Web;

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

        $shelves = $warehouse->shelves()->orderBy('shelf_code')->get();

        return view('shelves.index', compact('branch', 'warehouse', 'shelves'));
    }

    /** Raf tek alanlı (shelf_code) olduğu için ayrı bir create sayfası yok, liste üstünde inline form var. */
    public function store(Request $request, Branch $branch, Warehouse $warehouse)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);

        $data = $request->validate([
            'shelf_code' => 'required|string|max:50',
        ]);

        if ($warehouse->shelves()->where('shelf_code', $data['shelf_code'])->exists()) {
            return back()->withErrors(['shelf_code' => 'Bu raf ID bu depoda zaten kullanılıyor.']);
        }

        $warehouse->shelves()->create($data);

        return back()->with('status', 'Raf oluşturuldu.');
    }

    public function edit(Branch $branch, Warehouse $warehouse, Shelf $shelf)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);
        $this->ensureBelongsToWarehouse($warehouse, $shelf);

        return view('shelves.edit', compact('branch', 'warehouse', 'shelf'));
    }

    public function update(Request $request, Branch $branch, Warehouse $warehouse, Shelf $shelf)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);
        $this->ensureBelongsToWarehouse($warehouse, $shelf);

        $data = $request->validate([
            'shelf_code' => 'required|string|max:50',
        ]);

        if ($warehouse->shelves()->where('shelf_code', $data['shelf_code'])->where('id', '!=', $shelf->id)->exists()) {
            return back()->withErrors(['shelf_code' => 'Bu raf ID bu depoda zaten kullanılıyor.'])->withInput();
        }

        $shelf->update($data);

        return redirect()->route('shelves.index', [$branch, $warehouse])->with('status', 'Raf güncellendi.');
    }

    /** Doküman kuralı: raf silinmez, aktif/pasif arasında geçiş yapılır. */
    public function toggle(Branch $branch, Warehouse $warehouse, Shelf $shelf)
    {
        $this->ensureBelongsToBranch($branch, $warehouse);
        $this->ensureBelongsToWarehouse($warehouse, $shelf);

        $shelf->update(['is_active' => ! $shelf->is_active]);

        return back()->with('status', $shelf->is_active ? 'Raf aktif yapıldı.' : 'Raf pasif yapıldı.');
    }

    private function ensureBelongsToBranch(Branch $branch, Warehouse $warehouse): void
    {
        abort_if($warehouse->branch_id !== $branch->id, 404);
    }

    private function ensureBelongsToWarehouse(Warehouse $warehouse, Shelf $shelf): void
    {
        abort_if($shelf->warehouse_id !== $warehouse->id, 404);
    }
}
