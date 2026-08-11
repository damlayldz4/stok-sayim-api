<?php

namespace App\Http\Controllers\Web;

use App\Enums\StockCountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockCount;
use App\Models\User;
use App\Services\StockCountReportService;
use Illuminate\Http\Request;

class StockCountController extends Controller
{
    public function index()
    {
        $stockCounts = StockCount::with(['branch', 'warehouse'])->latest()->paginate(20);

        return view('stock-counts.index', compact('stockCounts'));
    }

    public function create(Request $request)
    {
        // Şube -> depo -> raf hiyerarşisini JS tarafında (cascading select)
        // kullanmak için tek seferde iç içe yüklüyoruz.
        $branches = Branch::with(['warehouses' => function ($q) {
            $q->where('is_active', true)->with(['shelves' => fn ($q) => $q->where('is_active', true)]);
        }])->where('is_active', true)->orderBy('name')->get();

        $staff = User::where('company_id', $request->user()->company_id)
            ->where('role', UserRole::CountStaff)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock-counts.create', compact('branches', 'staff'));
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

        return redirect()->route('stock-counts.show', $stockCount)->with('status', 'Sayım oluşturuldu.');
    }

    /** Sayım sonuçları ekranı — okutulmuş + sayılmamış ürünleri gösterir. */
    public function show(Request $request, StockCount $stockCount, StockCountReportService $reportService)
    {
        $stockCount->load(['branch', 'warehouse', 'shelves', 'assignedUsers']);

        $filters = $request->validate([
            'result' => 'nullable|array',
            'result.*' => 'in:eksik,fazla,eslesen,sayilmamis',
            'barcode' => 'nullable|string',
        ]);

        $rows = $reportService->buildRows($stockCount, $filters);

        return view('stock-counts.show', [
            'stockCount' => $stockCount,
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function start(StockCount $stockCount)
    {
        abort_if($stockCount->status !== StockCountStatus::Draft, 422, 'Sadece taslak durumundaki sayımlar başlatılabilir.');

        $stockCount->update(['status' => StockCountStatus::InProgress]);

        return back()->with('status', 'Sayım başlatıldı, sayım personeli artık mobil uygulamadan kayıt girebilir.');
    }

    public function complete(StockCount $stockCount)
    {
        abort_if($stockCount->status === StockCountStatus::Completed, 422, 'Sayım zaten tamamlanmış.');

        $stockCount->update(['status' => StockCountStatus::Completed]);

        return back()->with('status', 'Sayım tamamlandı.');
    }
}
