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
    public function getExcel(Request $request, sqlExcelService $sqlExcelService)
    {
        //处理sql字符串
        $this->parseXml($request->input('xml'));

        //每张表需要生成的数据量
        $rows = $request->input('quantity');
        if (!$rows) $rows = count($this->getWhere()) + 2;

        //生成excel的保存路径
        $savePath = $this->getSavePath($this->getId(), $request->input('excelNum'));

        //需要标红的单元格数组
        $redFields = $this->getAllRedFields();
        //需要标橙的单元格数组
        $orangeFields = $this->getAllOrangeFields();

        $sqlExcelService = $sqlExcelService->getSqlExcel(array_column($this->getFrom(), 'name'));
        foreach ($sqlExcelService->getWorksheetIterator() as $key => $sqlSheet) {
            $sheetTitle = $sqlSheet->getTitle();
            $sqlSheet->addSqlRows($rows - 1)
                ->uniqueSqlRows()
                ->redData($redFields[$sheetTitle])
                ->orangeData($orangeFields[$sheetTitle])
                ->setSelectedCell('A1');
        }
        $sqlExcelService->saveSqlExcel($savePath);

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
                        $whereFields[$key] = array_column(array_column($this->parseWhere($whereFields[$key]), 0), 1);
                    } else {
                        $whereFields[$key] = explode(',', str_replace(' ', '', $whereFields[$key]));
                    }
                    $sheet->redData($whereFields[$key]);
                }

                if ($selectFields[$key]) {
                    $selectFields[$key] = array_column($this->parseSelect($selectFields[$key]), 1);
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
        if (count($this->getFrom()) > 1) return false;

        $num = $request->input('num');
        $inputs = $this->getCodeInputs($num);
        $outputs = $this->getCodeOutputs($num);
        $code = $codeService->makeSelectCode(substr($this->getId(), 4), $num, $inputs, $outputs);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    public function getCodeByTables(Request $request, CodeService $codeService)
    {
        $num = $request->input('num');
        $id = $request->input('id');
        $inputs = $this->parseInputs($request->input('inputs'));
        $resultMap = $this->parseResultMapXml($request->input('assertions'));
        $savePath = $this->getSavePath($id, $num);
        $assertions = $this->getLastRowValues($savePath, $resultMap, $inputs);
        $code = $codeService->makeSelectCode(substr($id, 4), $num, $inputs, $assertions);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    /**
     * 获取代码输入字段集合
     * @return array
     */
    private function getCodeInputs($num)
    {
        $savePath = $this->getSavePath($this->getId(), $num);
        $fields = $this->toClassify($this->getWhereParameters());
        $result = $this->getLastRowValues($savePath, $fields);

        return $result;
    }

    /**
     * 获取代码输出字段集合
     * @return array
     */
    private function getCodeOutputs($num)
    {
        $savePath = $this->getSavePath($this->getId(), $num);
        $fields = $this->toClassify($this->getSelect());
        $result = $this->getLastRowValues($savePath, $fields);

        return $result;
    }

    private function getLastRowValues($excelPath, $fields)
    {
        if (Storage::disk('local')->exists($excelPath)) {
            $sqlExcel = IOFactory::load(sqlExcelService::getAPath($excelPath));
            $excelField = null;
            $highestRow = null;
            $result = [];

            foreach ($sqlExcel->getWorksheetIterator() as $key => $sheet) {
                $sheetTitle = $sheet->getTitle();
                foreach ($sheet->getColumnIterator() as $columnIndex => $column) {
                    $excelField = $sheet->getCell($columnIndex . 1)->getValue();
                    foreach ($fields[$sheetTitle] as $key => $value) {
                        if ($excelField == $value['name']) {
                            $highestRow = $sheet->getHighestRow();
                            $value['value'] = $sheet->getCell($columnIndex . $highestRow)->getValue();
                            $result[] = $value;
                        }
                    }
                }
            }
        }

        return $result;
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
