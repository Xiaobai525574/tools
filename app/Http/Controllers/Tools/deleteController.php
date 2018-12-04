<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
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
    public function getExcel(Request $request)
    {
        $this->setSql($request->input('sql'));
        $savePath = config('tools.storage.deletePath') . 'exec' . $this->getId() . '_001.xlsx';
        $tablePath = config('tools.storage.tablesPath') . $this->getTable() . '.xlsx';;
        if (!$this->exportExcel($savePath, $tablePath, count($this->getWheres()) + 2)) {
            return redirect()->back()->with('table', 'notExists')->withInput();
        }

        return Storage::download($savePath);
    }

    public function getCode(Request $request)
    {
        return 233;
    }

}
