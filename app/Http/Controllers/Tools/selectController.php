<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\sqlExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function getExcel(Request $request)
    {
        //处理sql字符串
        $this->setSql($request->input('sql'));
        //每张表需要生成的数据量
        $rows = $request->input('quantity');
        //生成excel的保存路径
        $savePath = config('tools.storage.selectPath')
            . str_replace('**', $this->getId(), $request->input('excelName')) . '.'
            . config('tools.excel.type');

        if (count($this->getTables()) > 1) {
            $this->saveTablesExcel($savePath, $rows);
        } else {
            $this->saveTableExcel($savePath, $rows);
        }

        return Storage::download($savePath);
    }

    public function getExcelByTables(Request $request, sqlExcelService $sqlExcel)
    {
        if ($request->method() == 'GET') return view('select/getExcelByTables');

        //生成excel的保存路径
        $savePath = config('tools.storage.selectPath')
            . str_replace('**', $request->input('id'), $request->input('excelName')) . '.'
            . config('tools.excel.type');
        $rows = $request->input('tableRows');
        $whereFields = $request->input('tableWheres');
        $selectFields = $request->input('tableSelects');
        $sqlExcel->getSqlExcel($request->input('tableNames'));
        if ($sqlExcel->getSheetNames()[0] != 'sqlSheet') {
            foreach ($sqlExcel->getWorksheetIterator() as $key => $sheet) {
                if ($whereFields[$key]) $whereFields[$key] = explode(',', $whereFields[$key]);
                if ($selectFields[$key]) $selectFields[$key] = explode(',', $selectFields[$key]);
                $sheet->addRows($rows[$key] - 1)
                    ->uniqueRows()
                    ->redData($whereFields[$key])
                    ->orangeData($selectFields[$key]);
            }
            $sqlExcel->setActiveSheetIndex(0);
        }
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    public function saveTableExcel($savePath, $rows = null)
    {
        if (!$rows) $rows = count($this->getWheres()) + 2;
        //需要标红的单元格数组
        $redFields = array_column($this->getWheres(), 0);
        //需要表橙的单元格数组
        $orangeFields = $this->getSelects();

        $sqlExcel = resolve(sqlExcelService::class);
        $sqlExcel->getSqlExcel(array_column($this->getTables(), 0))
            ->getSheet(0)
            ->addRows($rows - 1)
            ->uniqueRows()
            ->redData($redFields)
            ->orangeData($orangeFields);
        $sqlExcel->saveSqlExcel($savePath);
    }

    public function saveTablesExcel()
    {

    }

}

/**code is far away from bug with the animal protecting
 *  ┏┓　　　┏┓
 *┏┛┻━━━┛┻┓
 *┃　　　　　　　┃ 　
 *┃　　　━　　　┃
 *┃　┳┛　┗┳　┃
 *┃　　　　　　　┃
 *┃　　　┻　　　┃
 *┃　　　　　　　┃
 *┗━┓　　　┏━┛
 *　　┃　　　┃神兽保佑
 *　　┃　　　┃代码无BUG！
 *　　┃　　　┗━━━┓
 *　　┃　　　　　　　┣┓
 *　　┃　　　　　　　┏┛
 *　　┗┓┓┏━┳┓┏┛
 *　　　┃┫┫　┃┫┫
 *　　　┗┻┛　┗┻┛
 *　　　
 */
