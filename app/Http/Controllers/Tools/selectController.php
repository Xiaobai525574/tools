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
        $excelName = $request->input('table') . '.xlsx';
        $rowNum = $request->input('rowNum');

        $excelPath = $this->exportExcel($excelName, $rowNum);

        if (!$excelPath) return redirect()->back()->with('table', 'notExists')->withInput();
        return Storage::download($excelPath);
    }

    /**
     * save Excel
     * @param $tableExcel
     * @param $excelName
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    protected function saveExcel($tableExcel, $excelName)
    {
        //todo:路径格式不对
        $excelWriter = PHPExcel_IOFactory::createWriter($tableExcel, 'Excel2007');
        $path = storage_path("app\\public\\select\\") . $excelName;
        if (Storage::disk('local')->exists($path)) Storage::delete($path);

        $excelWriter->save($path);
    }
}
