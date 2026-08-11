<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Admin'in seçtiği kolonlara göre değişen, herhangi bir rapor için
 * kullanılabilecek genel Excel export sınıfı. Rapor mantığı burada
 * değil, StockCountReportService'te — bu sınıf sadece hazır veriyi
 * xlsx'e döküyor.
 */
class GenericArrayExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly array $rows,
        private readonly array $headings,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
