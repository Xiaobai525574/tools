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
     * @param sqlExcelService $sqlExcel
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcel(Request $request, sqlExcelService $sqlExcel)
    {
        //处理sql字符串
        $this->setSql($request->input('sql'));
        //生成excel的保存路径
        $savePath = config('tools.storage.deletePath') . 'setup_' . $this->getId() . '_001.xlsx';
        //每张表需要生成的数据量
        $quantity = $request->input('quantity') or $quantity = count($this->getWheres()) + 2;
        //需要标红的单元格数组
        $fields = array_column($this->getWheres(), 0);

        $sqlExcel->getSqlExcel(array_column($this->getTables(), 0));
        $sqlExcel->getSheet(0)
            ->addRows($quantity - 1)
            ->uniqueRows()
            ->redData($fields);
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    public function getCode(Request $request)
    {
        return 233;
    }

}
