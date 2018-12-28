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
     * @param sqlExcelService $sqlExcelService
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
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
        foreach ($sqlExcelService->getSqlSheetIterator() as $key => $sqlSheet) {
            $sheetTitle = $sqlExcelService->getActualName($sqlSheet->getTitle());
            $sqlSheet->addSqlRows($rows - 1)
                ->uniqueSqlRows()
                ->redData($redFields[$sheetTitle])
                ->orangeData($orangeFields[$sheetTitle])
                ->setSelectedCell('A1');
        }
        $sqlExcelService->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    /**
     * 通过参数创建excel
     * @param Request $request
     * @param sqlExcelService $sqlExcel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcelByParameters(Request $request, sqlExcelService $sqlExcel)
    {
        if ($request->method() == 'GET') return view('select/getExcelByTables');
        $tables = $this->formatInput($request->input('tables'));
        //生成excel的保存路径
        $savePath = $this->getSavePath($request->input('id'), $request->input('excelNum'));
        $sqlExcel->getSqlExcel(array_keys($tables));
        if ($sqlExcel->getSheetNames()[0] == 'sqlSheet') abort(403, '数据库表未找到');
        foreach ($sqlExcel->getSqlSheetIterator() as $key => $sheet) {
            $actualName = $sqlExcel->getActualName($sheet->getTitle());
            $table = $tables[$actualName];
            $sheet->addSqlRows($table['rows'] - 1)
                ->uniqueSqlRows()
                ->redData($table['red'])
                ->orangeData($table['orange'])
                ->setSelectedCell('A1');
        }
        $sqlExcel->setActiveSheetIndex(0);
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    /**
     * 获取代码模板
     * @param Request $request
     * @param CodeService $codeService
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getCode(Request $request, CodeService $codeService)
    {
        //处理sql字符串
        $this->parseXml($request->input('xml'));

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

    /**
     * 通过参数获取模板
     * @param Request $request
     * @param CodeService $codeService
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getCodeByParameters(Request $request, CodeService $codeService)
    {
        $num = $request->input('num');
        $id = $request->input('id');
        $inputs = $this->parseInputsParameter($request->input('inputs'));
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
     * 格式化输入数据
     * @param $input
     * @return mixed
     */
    private function formatInput($input)
    {
        $result = [];
        foreach ($input as $key => $value) {
            $value['red'] = $this->parseRedParameter($value['red']);
            $value['orange'] = $this->parseOrangeParameter($value['orange']);
            $result[$value['name']] = $value;
        }

        return $result;
    }

    /**
     * 获取代码输入字段集合
     * @param $num
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
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
     * @param $num
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getCodeOutputs($num)
    {
        $savePath = $this->getSavePath($this->getId(), $num);
        $fields = $this->getSelect();
        $result = [];
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) {
                $result[] = $fields[$key];
                unset($fields[$key]);
            }
        }
        $fields = $this->toClassify($fields);
        $result = array_merge($result, $this->getLastRowValues($savePath, $fields));

        return $result;
    }

    /**
     * 根据所给字段名集合，获取excel最后一行对应的值
     * @param $excelPath
     * @param $fields
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getLastRowValues($excelPath, $fields)
    {
        if (Storage::disk('local')->exists($excelPath)) {
            $sqlExcel = IOFactory::load(sqlExcelService::getAPath($excelPath));
            $sqlExcel = new sqlExcelService($sqlExcel);
            $excelField = null;
            $highestRow = null;
            $result = [];

            foreach ($sqlExcel->getSqlSheetIterator() as $key => $sheet) {
                $sheetTitle = $sqlExcel->getActualName($sheet->getTitle());
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

    /**
     * 获取Excel保存路径
     * @param $id
     * @param $num
     * @return string
     */
    private function getSavePath($id, $num)
    {
        return config('tools.storage.selectPath') . 'setup_'
            . $id . '_' . $num . '.' . config('tools.excel.type');
    }

    /**
     * 解析传入参数：标红字段
     * @param $red
     * @return array
     */
    private function parseRedParameter($red)
    {
        if (strpos($red, '=') !== false) {
            $red = $this->parseWhere($red, true)->getWhereFields();
        } else {
            $red = explode(',', str_replace(' ', '', $red));
            foreach ($red as $key => &$value) {
                $value = ['name' => $value];
            }
        }

        return $red;
    }

    /**
     * 解析传入参数：标橙字段
     * @param $orange
     * @return array
     */
    private function parseOrangeParameter($orange)
    {
        return $this->parseSelect($orange, true)->getSelectFields();
    }

    /**
     * 解析传入的inputs参数
     * @param $inputs
     * @return array
     */
    private function parseInputsParameter($inputs)
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
