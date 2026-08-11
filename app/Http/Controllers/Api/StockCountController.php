<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockCountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\StockCount;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    /** Admin için tüm sayımlar, sayım personeli için yalnızca atanmış sayımlar. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = StockCount::with(['branch', 'warehouse']);

        if ($user->role === UserRole::CountStaff) {
            $query->whereHas('assignedUsers', fn ($q) => $q->where('users.id', $user->id));
        }

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shelf_ids' => 'array',
            'shelf_ids.*' => 'exists:shelves,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $stockCount = StockCount::create([
            'name' => $data['name'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => StockCountStatus::Draft,
            'created_by' => $request->user()->id,
        ]);

        if (! empty($data['shelf_ids'])) {
            $stockCount->shelves()->sync($data['shelf_ids']);
        }

        if (! empty($data['user_ids'])) {
            $stockCount->assignedUsers()->sync(
                collect($data['user_ids'])->mapWithKeys(fn ($id) => [$id => ['assigned_at' => now()]])
            );
        }

        return response()->json($stockCount->load(['shelves', 'assignedUsers']), 201);
    }

    public function show(Request $request, StockCount $stockCount)
    {
        $this->ensureAccess($request, $stockCount);

        return $stockCount->load(['branch', 'warehouse', 'shelves', 'assignedUsers']);
    }

    public function update(Request $request, StockCount $stockCount)
    {
        abort_if(
            $stockCount->status === StockCountStatus::Completed,
            422,
            'Tamamlanmış sayım değiştirilemez.'
        );

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:draft,in_progress,cancelled',
            'shelf_ids' => 'array',
            'shelf_ids.*' => 'exists:shelves,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $stockCount->update(collect($data)->except(['shelf_ids', 'user_ids'])->all());

        if (array_key_exists('shelf_ids', $data)) {
            $stockCount->shelves()->sync($data['shelf_ids']);
        }

        if (array_key_exists('user_ids', $data)) {
            $stockCount->assignedUsers()->sync(
                collect($data['user_ids'])->mapWithKeys(fn ($id) => [$id => ['assigned_at' => now()]])
            );
        }

        return $stockCount->load(['shelves', 'assignedUsers']);
    }

    public function complete(StockCount $stockCount)
    {
        abort_if(
            $stockCount->status === StockCountStatus::Completed,
            422,
            'Sayım zaten tamamlanmış.'
        );

        $stockCount->update(['status' => StockCountStatus::Completed]);

        return response()->json(['message' => 'Sayım tamamlandı.']);
    }

    /** Sayım personeli yalnızca kendisine atanmış sayıma erişebilir. */
    private function ensureAccess(Request $request, StockCount $stockCount): void
    {
        $user = $request->user();

        if ($user->role !== UserRole::CountStaff) {
            return;
        }

        $isAssigned = $stockCount->assignedUsers()->where('users.id', $user->id)->exists();

        abort_if(! $isAssigned, 403, 'Bu sayıma erişim yetkiniz yok.');
    }
}
