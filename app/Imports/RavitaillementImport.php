<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class RavitaillementImport implements ToArray
{
    public function array(array $rows)
    {
        return $rows;
    }
} 