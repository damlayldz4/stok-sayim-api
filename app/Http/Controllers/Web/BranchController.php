<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('warehouses')->orderBy('name')->paginate(20);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if (Branch::where('branch_code', $data['branch_code'])->exists()) {
            return back()->withErrors(['branch_code' => 'Bu şube ID zaten kullanılıyor.'])->withInput();
        }

        Branch::create($data);

        return redirect()->route('branches.index')->with('status', 'Şube oluşturuldu.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /** branch_code kasıtlı olarak burada güncellenmiyor (API katmanıyla tutarlı). */
    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $branch->update($data);

        return redirect()->route('branches.index')->with('status', 'Şube güncellendi.');
    }

    /** Doküman kuralı: şube silinmez, aktif/pasif arasında geçiş yapılır. */
    public function toggle(Branch $branch)
    {
        $branch->update(['is_active' => ! $branch->is_active]);

        return back()->with('status', $branch->is_active ? 'Şube aktif yapıldı.' : 'Şube pasif yapıldı.');
    }
}
