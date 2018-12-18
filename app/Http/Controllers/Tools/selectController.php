<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\CodeService;
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
        $this->parseXml($request->input('xml'));
        if (count($this->getTables()) > 1) return false;

        //每张表需要生成的数据量
        $rows = $request->input('quantity');
        if (!$rows) $rows = count($this->getWheres()) + 2;

        //生成excel的保存路径
        $savePath = config('tools.storage.selectPath')
            . str_replace('**', $this->getId(), $request->input('excelName')) . '.'
            . config('tools.excel.type');

        //需要标红的单元格数组
        $redFields = array_column(array_column($this->getWheres(), 0), 1);
        //需要标橙的单元格数组
        $orangeFields = array_column($this->getSelects(), 1);

        $sqlExcel = resolve(sqlExcelService::class);
        $sqlExcel->getSqlExcel(array_column($this->getTables(), 0))
            ->getSheet(0)
            ->addRows($rows - 1)
            ->uniqueRows()
            ->redData($redFields)
            ->orangeData($orangeFields)
            ->setSelectedCell('A1');
        $sqlExcel->saveSqlExcel($savePath);

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
                $sheet->addRows($rows[$key] - 1)
                    ->uniqueRows();

                if ($whereFields[$key]) {
                    if (strpos($whereFields[$key], '=') !== false) {
                        $whereFields[$key] = array_column(array_column($this->parseWheres($whereFields[$key]), 0), 1);
                    } else {
                        $whereFields[$key] = explode(',', str_replace(' ', '', $whereFields[$key]));
                    }
                    $sheet->redData($whereFields[$key]);
                }

                if ($selectFields[$key]) {
                    $selectFields[$key] = array_column($this->parseSelects($selectFields[$key]), 1);
                    $sheet->orangeData($selectFields[$key]);
                }

                $sheet->setSelectedCell('A1');
            }
            $sqlExcel->setActiveSheetIndex(0);
        }
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    public function getCode(Request $request, CodeService $codeService)
    {
        //处理sql字符串
        $this->parseXml($request->input('xml'));
        if (count($this->getTables()) > 1) return false;

        $wheres = array_column($this->getWheres(), 2);
        foreach ($wheres as $key => &$where) {
            if (strpos($where, '#{') !== false) {
                $where = substr($where, 2, strpos($where, ',') - 2);
            } else {
                unset($wheres[$key]);
            }
        }

        $assertions = array_column($this->getResultMap(), 'property');
        $code = $codeService->makeSelectCode(substr($this->getId(), 4)
            , $request->input('num'), $wheres, $assertions);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    public function getCodeByTables(Request $request, CodeService $codeService)
    {
        $inputs = $this->parseInputs($request->input('inputs'));
        $assertions = array_column($this->parseResultMapXml($request->input('assertions')), 'property');
        $code = $codeService->makeSelectCode(substr($request->input('id'), 4)
            , $request->input('num'), $inputs, $assertions);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    private function parseInputs($inputs)
    {
        $inputsArr = [];
        do {
            $inputs = substr($inputs, strpos($inputs, '#{') + 2);
            $inputsArr[] = substr($inputs, 0, strpos($inputs, ','));
        } while (strpos($inputs, '#{') !== false);

        return $inputsArr;
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
