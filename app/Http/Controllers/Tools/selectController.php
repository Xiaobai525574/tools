<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\CodeService;
use App\Http\Services\sqlExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $savePath = $this->getSavePath($this->getId(), $request->input('excelNum'));

        //需要标红的单元格数组
        $redFields = array_column(array_column($this->getWheres(), 0), 1);
        //需要标橙的单元格数组
        $orangeFields = array_column($this->getSelects(), 1);

        $sqlExcel = resolve(sqlExcelService::class);
        $sqlExcel->getSqlExcel(array_column($this->getTables(), 0))
            ->getSheet(0)
            ->addSqlRows($rows - 1)
            ->uniqueSqlRows()
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
        $savePath = $this->getSavePath($request->input('id'), $request->input('excelNum'));
        $rows = $request->input('tableRows');
        $whereFields = $request->input('tableWheres');
        $selectFields = $request->input('tableSelects');
        $sqlExcel->getSqlExcel($request->input('tableNames'));
        if ($sqlExcel->getSheetNames()[0] != 'sqlSheet') {
            foreach ($sqlExcel->getWorksheetIterator() as $key => $sheet) {
                $sheet->addSqlRows($rows[$key] - 1)
                    ->uniqueSqlRows();

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

        $num = $request->input('num');
        $savePath = $this->getSavePath($this->getId(), $num);
        $assertions = $this->getLastRowValuesByFields($this->getResultMap(), $savePath);
        $code = $codeService->makeSelectCode(substr($this->getId(), 4)
            , $num, $wheres, $assertions);

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

    private function getLastRowValuesByFields($fields, $excelPath)
    {
        if (Storage::disk('local')->exists($excelPath)) {
            $sheet = IOFactory::load(sqlExcelService::getAPath($excelPath))->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            foreach ($sheet->getColumnIterator() as $columnIndex => $column) {
                $excelField = $sheet->getCell($columnIndex . 1)->getValue();
                foreach ($fields as $key => &$field) {
                    if ($excelField == $field['column']) {
                        $field['value'] = $sheet->getCell($columnIndex . $highestRow)->getValue();
                    }
                }
            }
        }

        return $fields;
    }

    private function getSavePath($id, $num)
    {
        return config('tools.storage.selectPath') . 'setup_'
            . $id . '_' . $num . '.' . config('tools.excel.type');
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
