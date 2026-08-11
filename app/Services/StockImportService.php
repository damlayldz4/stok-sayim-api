<?php

namespace App\Services;

use App\Enums\StockImportMode;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Shelf;
use App\Models\StockImport;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StockImportService
{
    /**
     * Zorunlu CSV kolonları. Doküman "kesin kolon isimleri örnek CSV
     * dosyası üzerinden belirlenecektir" diyor — şimdilik bu isimlerle
     * ilerliyoruz, örnek dosya gelince kolayca güncellenebilir.
     */
    private const REQUIRED_COLUMNS = [
        'product_name',
        'barcode',
        'expected_quantity',
        'branch_code',
        'warehouse_code',
    ];

    private const OPTIONAL_COLUMNS = [
        'product_code',
        'shelf_code',
    ];

    public function process(UploadedFile $file, string $mode, Company $company, User $user, bool $confirm): array
    {
        $rows = $this->readCsv($file);

        if (isset($rows['format_error'])) {
            return [
                'status' => 'format_error',
                'message' => $rows['format_error'],
            ];
        }

        [$header, $dataRows] = [$rows['header'], $rows['rows']];

        $missingColumns = array_diff(self::REQUIRED_COLUMNS, $header);
        if (! empty($missingColumns)) {
            return [
                'status' => 'format_error',
                'message' => 'Zorunlu kolonlar eksik: '.implode(', ', $missingColumns),
            ];
        }

        [$validRows, $errors] = $this->validateRows($header, $dataRows, $company);

        $totalRows = count($dataRows);
        $successCount = count($validRows);
        $errorCount = count($errors);

        // Replace modunda, önce hangi ürünlerin pasif yapılacağını hesapla
        // ve onay alınmadıysa DB'ye dokunmadan kullanıcıya göster.
        $deactivateBarcodes = [];
        if ($mode === StockImportMode::Replace->value) {
            $csvBarcodes = array_column($validRows, 'barcode');
            $deactivateBarcodes = Product::where('company_id', $company->id)
                ->where('is_active', true)
                ->whereNotIn('barcode', $csvBarcodes)
                ->pluck('barcode')
                ->all();

            if (! $confirm) {
                return [
                    'status' => 'confirmation_required',
                    'message' => 'Replace modu onay bekliyor. Bu işlem '.count($deactivateBarcodes).
                        ' ürünü pasif yapacak. Onaylamak için isteği confirm=true ile tekrar gönder.',
                    'total_rows' => $totalRows,
                    'success_rows' => $successCount,
                    'error_rows' => $errorCount,
                    'errors' => $errors,
                    'will_deactivate_count' => count($deactivateBarcodes),
                    'will_deactivate_barcodes' => $deactivateBarcodes,
                ];
            }
        }

        $stockImport = DB::transaction(function () use ($validRows, $mode, $deactivateBarcodes, $company, $user, $file, $totalRows, $successCount, $errorCount, $errors) {
            foreach ($validRows as $row) {
                Product::updateOrCreate(
                    ['company_id' => $company->id, 'barcode' => $row['barcode']],
                    [
                        'product_name' => $row['product_name'],
                        'product_code' => $row['product_code'] ?: null,
                        'expected_quantity' => $row['expected_quantity'],
                        'branch_id' => $row['branch_id'],
                        'warehouse_id' => $row['warehouse_id'],
                        'shelf_id' => $row['shelf_id'],
                        'is_active' => true,
                    ]
                );
            }

            if ($mode === StockImportMode::Replace->value && ! empty($deactivateBarcodes)) {
                Product::where('company_id', $company->id)
                    ->whereIn('barcode', $deactivateBarcodes)
                    ->update(['is_active' => false]);
            }

            return StockImport::create([
                'company_id' => $company->id,
                'imported_by' => $user->id,
                'mode' => $mode,
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => $totalRows,
                'success_rows' => $successCount,
                'error_rows' => $errorCount,
                'error_details' => $errors,
            ]);
        });

        return [
            'status' => 'completed',
            'import' => $stockImport,
            'total_rows' => $totalRows,
            'success_rows' => $successCount,
            'error_rows' => $errorCount,
            'errors' => $errors,
            'deactivated_count' => count($deactivateBarcodes),
        ];
    }

    /** CSV'yi okuyup başlık satırını ve veri satırlarını döner. */
    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return ['format_error' => 'Dosya okunamadı.'];
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return ['format_error' => 'Dosya CSV formatında değil veya boş.'];
        }

        $header = array_map(fn ($h) => trim(strtolower($h)), $header);

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            // Tamamen boş satırları atla
            if (count($line) === 1 && trim((string) $line[0]) === '') {
                continue;
            }

            $rows[] = array_combine(
                array_slice($header, 0, count($line)),
                $line
            );
        }

        fclose($handle);

        return ['header' => $header, 'rows' => $rows];
    }

    /**
     * Her satırı doküman md.8'deki kurallara göre doğrular.
     * Dönüş: [geçerli satırlar (çözümlenmiş branch/warehouse/shelf id'leriyle), hatalar]
     */
    private function validateRows(array $header, array $rows, Company $company): array
    {
        $valid = [];
        $errors = [];
        $seenBarcodes = [];

        // Şube/depo/raf kodlarını tekrar tekrar sorgulamamak için önbellek
        $branchCache = [];
        $warehouseCache = [];
        $shelfCache = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // 1. satır başlık, veri 2'den başlar

            $barcode = trim((string) ($row['barcode'] ?? ''));
            $productName = trim((string) ($row['product_name'] ?? ''));
            $productCode = trim((string) ($row['product_code'] ?? ''));
            $expectedQuantityRaw = trim((string) ($row['expected_quantity'] ?? ''));
            $branchCode = trim((string) ($row['branch_code'] ?? ''));
            $warehouseCode = trim((string) ($row['warehouse_code'] ?? ''));
            $shelfCode = trim((string) ($row['shelf_code'] ?? ''));

            if ($barcode === '') {
                $errors[] = $this->error($lineNumber, 'Barkod boş.', $barcode, $productName);
                continue;
            }

            if (isset($seenBarcodes[$barcode])) {
                $errors[] = $this->error($lineNumber, 'Barkod dosya içinde tekrar ediyor.', $barcode, $productName);
                continue;
            }

            if ($productName === '') {
                $errors[] = $this->error($lineNumber, 'Ürün adı boş.', $barcode, $productName);
                continue;
            }

            if (! is_numeric($expectedQuantityRaw) || (int) $expectedQuantityRaw < 0) {
                $errors[] = $this->error($lineNumber, 'Beklenen miktar geçerli bir sayı değil.', $barcode, $productName);
                continue;
            }

            if (! isset($branchCache[$branchCode])) {
                $branchCache[$branchCode] = Branch::where('company_id', $company->id)
                    ->where('branch_code', $branchCode)
                    ->first();
            }
            $branch = $branchCache[$branchCode];

            if (! $branch) {
                $errors[] = $this->error($lineNumber, "Şube bulunamadı: {$branchCode}", $barcode, $productName);
                continue;
            }

            $warehouseCacheKey = $branch->id.'|'.$warehouseCode;
            if (! isset($warehouseCache[$warehouseCacheKey])) {
                $warehouseCache[$warehouseCacheKey] = Warehouse::where('branch_id', $branch->id)
                    ->where('warehouse_code', $warehouseCode)
                    ->first();
            }
            $warehouse = $warehouseCache[$warehouseCacheKey];

            if (! $warehouse) {
                $errors[] = $this->error($lineNumber, "Depo bulunamadı: {$warehouseCode}", $barcode, $productName);
                continue;
            }

            $shelfId = null;
            if ($shelfCode !== '') {
                $shelfCacheKey = $warehouse->id.'|'.$shelfCode;
                if (! isset($shelfCache[$shelfCacheKey])) {
                    $shelfCache[$shelfCacheKey] = Shelf::where('warehouse_id', $warehouse->id)
                        ->where('shelf_code', $shelfCode)
                        ->first();
                }
                $shelf = $shelfCache[$shelfCacheKey];

                if (! $shelf) {
                    $errors[] = $this->error($lineNumber, "Raf bulunamadı: {$shelfCode}", $barcode, $productName);
                    continue;
                }

                $shelfId = $shelf->id;
            }

            $seenBarcodes[$barcode] = true;

            $valid[] = [
                'barcode' => $barcode,
                'product_name' => $productName,
                'product_code' => $productCode,
                'expected_quantity' => (int) $expectedQuantityRaw,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'shelf_id' => $shelfId,
            ];
        }

        return [$valid, $errors];
    }

    private function error(int $line, string $reason, string $barcode, string $productName): array
    {
        return [
            'line' => $line,
            'reason' => $reason,
            'barcode' => $barcode ?: null,
            'product_name' => $productName ?: null,
        ];
    }
}
