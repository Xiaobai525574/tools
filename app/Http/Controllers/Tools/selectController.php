<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class selectController extends Controller
{
    public function index()
    {
        return view('select/index');
    }

    public function createExcel(Request $request)
    {
        $savePath = config('tools.storage.selectPath') . $request->input('table') . '.xlsx';
        $tablePath = config('tools.storage.tablesPath') . $request->input('table') . '.xlsx';;
        $rowNum = $request->input('rowNum');
        if (!$this->exportExcel($savePath, $tablePath, $rowNum)) return redirect()->back()->with('table', 'notExists')->withInput();

        return Storage::download($savePath);
    }

}
