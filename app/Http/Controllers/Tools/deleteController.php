<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class deleteController extends Controller
{
    public function createExcel() {
        $tableName = 'xiao';
        $tablePath = "public\\tables\\$tableName.xlsx";
        if (Storage::disk('local')->exists($tablePath)) {
            return Storage::download($tablePath);
        }
    }
}
