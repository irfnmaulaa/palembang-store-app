<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class MatchingStockImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        // skip to 2nd row
        if (strtoupper($row[0]) != 'NO') {
            $name  = $row[2];
            $code  = $row[3];
            $stock = $row[4];
        }

    }
}
