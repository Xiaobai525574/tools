<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class deleteController extends Controller
{
    public function index()
    {
        return view('delete/index');
    }

    public function createExcel(Request $request)
    {
        $tableName = $request->input('tableName');
        $tablePath = "public\\tables\\$tableName.xlsx";
        if (Storage::disk('local')->exists($tablePath)) {
            $tablePath = $this->doExcel($tablePath);
            return Storage::download($tablePath);
        } else {
            return redirect('delete/index')->with('table', 'notExists')->withInput();
        }
    }

    private function doExcel($tablePath)
    {

    }
}
