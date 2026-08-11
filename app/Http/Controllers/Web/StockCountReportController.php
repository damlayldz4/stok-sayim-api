<?php

namespace App\Http\Controllers\Web;

use App\Exports\GenericArrayExport;
use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Services\StockCountReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockCountReportController extends Controller
{
    public function __construct(private readonly StockCountReportService $service)
    {
    }

    public function index(Request $request, StockCount $stockCount)
    {
        [$filters, $columns] = $this->parseRequest($request);

        $rows = $this->service->buildRows($stockCount, $filters);
        $projected = $this->service->projectColumns($rows, $columns);

        return view('stock-counts.report', [
            'stockCount' => $stockCount,
            'availableColumns' => StockCountReportService::COLUMNS,
            'selectedColumns' => ! empty($columns) ? $columns : array_keys(StockCountReportService::COLUMNS),
            'rows' => $projected['data'],
            'headings' => $projected['headings'],
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, StockCount $stockCount)
    {
        [$filters, $columns] = $this->parseRequest($request);

        $rows = $this->service->buildRows($stockCount, $filters);
        $projected = $this->service->projectColumns($rows, $columns);

        $fileName = sprintf(
            'sayim-%d-raporu-%s.xlsx',
            $stockCount->id,
            now()->format('Y-m-d_His')
        );

        return Excel::download(
            new GenericArrayExport($projected['data'], $projected['headings']),
            $fileName
        );
    }

    private function parseRequest(Request $request): array
    {
        $data = $request->validate([
            'shelf_id' => 'nullable|exists:shelves,id',
            'product_id' => 'nullable|exists:products,id',
            'barcode' => 'nullable|string',
            'counted_by' => 'nullable|exists:users,id',
            'result' => 'nullable|array',
            'result.*' => 'in:eksik,fazla,eslesen,sayilmamis',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'columns' => 'nullable|array',
            'columns.*' => 'string',
        ]);

        $columns = $data['columns'] ?? [];
        unset($data['columns']);

        return [$data, $columns];
    }
}
