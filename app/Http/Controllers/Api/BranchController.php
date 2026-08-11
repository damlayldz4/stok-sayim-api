<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        // BelongsToCompany trait sayesinde otomatik olarak giriş yapan
        // kullanıcının kendi şirketinin şubeleriyle sınırlanır.
        return Branch::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if (Branch::where('branch_code', $data['branch_code'])->exists()) {
            return response()->json(['message' => 'Bu şube ID zaten kullanılıyor.'], 422);
        }

        $branch = Branch::create($data);

        return response()->json($branch, 201);
    }

    public function show(Branch $branch)
    {
        return $branch;
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($data);

        return $branch;
    }

    /** Doküman kuralı: şube silinmez, pasif yapılır. */
    public function destroy(Branch $branch)
    {
        $branch->update(['is_active' => false]);

        return response()->json(['message' => 'Şube pasif duruma getirildi.']);
    }
}
