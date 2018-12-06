<?php

namespace App\Http\Controllers\Tools;

use App\Http\Services\sqlExcelService;
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
    public function getExcel(Request $request, sqlExcelService $sqlExcel)
    {
        //处理sql字符串
        $this->setSql($request->input('sql'));
        //生成excel的保存路径
        $savePath = config('tools.storage.selectPath') . 'setup_' . $this->getId() . '_001.xlsx';
        //每张表需要生成的数据量
        $quantity = $request->input('quantity') or $quantity = count($this->getWheres()) + 2;
        //需要标红的单元格数组
        $fields = array_column($this->getWheres(), 0);

        $sqlExcel->getSqlExcel(array_column($this->getTables(), 0))
            ->addData($quantity)
            ->redData($fields)
            ->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

}
