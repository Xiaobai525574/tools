<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class selectController extends Controller
{
    /**
     * select 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('select/index');
    }

    /**
     * 创建excel并向浏览器提供下载
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createExcel(Request $request)
    {
        $savePath = config('tools.storage.selectPath') . $request->input('excelName') . '.xlsx';
        $tablePath = config('tools.storage.tablesPath') . $request->input('tableName') . '.xlsx';;
        $rowNum = $request->input('rowNum');
        if (!$this->exportExcel($savePath, $tablePath, $rowNum)) return redirect()->back()->with('table', 'notExists')->withInput();

        return Storage::download($savePath);
    }

}
