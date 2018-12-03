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
    public function createExcel(Request $request)
    {
        $sql = $this->sqlToArray($request->input('sql'));
        $savePath = config('tools.storage.deletePath') . $sql['table'] . '.xlsx';
        $tablePath = config('tools.storage.tablesPath') . $sql['table'] . '.xlsx';;
        if (!$this->exportExcel($savePath, $tablePath, count($sql['where']) + 2)) {
            return redirect()->back()->with('table', 'notExists')->withInput();
        }

        return Storage::download($savePath);
    }

}
