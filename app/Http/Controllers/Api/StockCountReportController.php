<?php

namespace App\Http\Controllers\Api;

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

    /**
     * Filtrelenmiş rapor önizlemesi (JSON). Web paneli, filtre/kolon
     * seçim ekranını doldurmak ve "indirmeden önce önizle" için bunu
     * kullanabilir.
     */
    public function preview(Request $request, StockCount $stockCount)
    {
        [$filters, $columns] = $this->parseRequest($request);

        $rows = $this->service->buildRows($stockCount, $filters);
        $projected = $this->service->projectColumns($rows, $columns);

        return response()->json([
            'available_columns' => StockCountReportService::COLUMNS,
            'selected_columns' => $columns ?: array_keys(StockCountReportService::COLUMNS),
            'row_count' => count($projected['data']),
            'rows' => $projected['data'],
        ]);
    }

    /** Seçilen filtre/kolonlarla Excel dosyasını indirir. */
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

    /** @return array{0: array, 1: array} [filtreler, seçilen kolonlar] */
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
