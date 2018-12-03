<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPExcel_IOFactory;

class selectController extends Controller
{
    public function index()
    {
        return view('select/index');
    }

    public function createExcel(Request $request)
    {
        $excelPath = config('tools.storage.selectPath') . $request->input('table') . '.xlsx';
        $rowNum = $request->input('rowNum');

        if (!$this->exportExcel($excelPath, $rowNum)) return redirect()->back()->with('table', 'notExists')->withInput();
        return Storage::download($excelPath);
    }

}
