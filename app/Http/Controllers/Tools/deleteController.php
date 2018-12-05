<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\sqlExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class deleteController extends Controller
{

    /**
     * delete首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('delete/index');
    }

    /**
     * 创建Excel
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function getExcel(Request $request, sqlExcelService $sqlExcel)
    {
        $this->setSql($request->input('sql'));
        $savePath = config('tools.storage.deletePath') . 'setup_' . $this->getId() . '_001.xlsx';
        $sqlExcel->getSqlExcel($this->getTables())
            ->sortFirstRow()
            ->addData()
            ->colorData()
            ->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    public function getCode(Request $request)
    {
        return 233;
    }

}
