<?php

namespace App\Enums;

enum StockImportMode: string
{
    case Upsert = 'upsert';
    case Replace = 'replace';
}
