<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\SqlCodeService\SqlCodeSelect;
use App\Http\Services\SqlExcelService\SqlExcel;
use App\Http\Services\SqlService\SqlSelect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
     * @param SqlSelect $select
     * @param SqlExcel $sqlExcel
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getExcel(Request $request, SqlSelect $select, SqlExcel $sqlExcel)
    {
        //处理sql字符串
        $select->parseXml($request->input('xml'));

        //每张表需要生成的数据量
        $rows = $request->input('quantity');
        if (!$rows) $rows = count($select->getWhere()) + 2;
        //生成excel的保存路径
        $savePath = $this->getSavePath($select->getId(), $request->input('excelNum'));
        //需要标红的单元格数组
        $redFields = $select->getWhereFieldsGroup();
        //需要标橙的单元格数组
        $orangeFields = $select->getSelectFieldsGroup();

        $tables = array_unique(array_column($select->getFrom(), 'name'));
        $sqlExcel = $sqlExcel->getSqlExcel($tables);

        foreach ($tables as $tableName) {
            $sheet = $sqlExcel->getSheetByActualName($tableName);
            $sheet->addSqlRows($rows - 1)
                ->uniqueSqlRows()
                ->redData($redFields[$tableName])
                ->orangeData($orangeFields[$tableName])
                ->setSelectedCell('A1');
        }
        $sqlExcel->setActiveSheetIndex(0);
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    /**
     * 通过参数创建excel
     * @param Request $request
     * @param sqlExcel $sqlExcel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcelByParameters(Request $request, sqlExcel $sqlExcel)
    {
        if ($request->method() == 'GET') return view('select/getExcelByTables');

        $id = trim($request->input('id'));
        $excelNum = $request->input('excelNum');
        $tables = $this->formatTables($request->input('tables'));
        //生成excel的保存路径
        $savePath = $this->getSavePath($id, $excelNum);
        $sqlExcel->getSqlExcel(array_keys($tables));
        if ($sqlExcel->getSheetNames()[0] == 'sqlSheet') abort(403, '数据库表未找到');

        foreach ($tables as $tableName => $table) {
            $sheet = $sqlExcel->getSheetByActualName($tableName);
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
     * @param SqlSelect $select
     * @param SqlCodeSelect $sqlCodeSelect
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getCode(Request $request, SqlSelect $select, SqlCodeSelect $sqlCodeSelect)
    {
        //处理sql字符串
        $select->parseXml($request->input('xml'));
        $excelNum = $request->input('excelNum');
        $result = [
            'status' => 'false',
            'info' => ''
        ];
        $excelPath = $this->getSavePath($select->getId(), $excelNum);
        if (Storage::disk('local')->exists($excelPath)) {
            $sqlExcel = IOFactory::load(SqlExcel::getAPath($excelPath));
            $sqlCodeSelect->setExcel($sqlExcel);
            $inputs = $select->getWhereParameters();
            $outputs = $select->getSelect();
            $code = $sqlCodeSelect->makeSelectCode(substr($select->getId(), 4), $excelNum, $inputs, $outputs);
            $result = [
                'status' => 'success',
                'info' => $code
            ];
        }

        return $result;
    }

    /**
     * 通过参数获取模板
     * @param Request $request
     * @param SqlCodeSelect $sqlCodeSelect
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getCodeByParameters(Request $request, SqlCodeSelect $sqlCodeSelect)
    {
        $num = $request->input('excelNum');
        $id = $request->input('id');
        $inputs = $this->parseInputsParameter($request->input('inputs'));
        $outputs = $this->parseOutputsParameter($request->input('outputs'));
        $code = $sqlCodeSelect->makeSelectCode(substr($id, 4), $num, $inputs, $outputs);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    /**
     * 格式化传入参数
     * @param $input
     * @return mixed
     */
    private function formatTables($input)
    {
        $result = [];
        foreach ($input as $key => $value) {
            $value['name'] = trim($value['name']);
            $value['rows'] = trim($value['rows']);
            $value['red'] = $this->parseRedParameter($value['red']);
            $value['orange'] = $this->parseOrangeParameter($value['orange']);
            $result[$value['name']] = $value;
        }

        return $result;
    }

    /**
     * 解析传入参数：标红字段
     * @param $red
     * @return array
     */
    private function parseRedParameter($red)
    {
        if (strpos($red, '=') !== false) {
            $selectSql = new SqlSelect();
            $red = $selectSql->where($red, true)->getWhereFields();
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
        $select = new SqlSelect();
        return $select->select($orange, true)->getSelectFields();
    }

    /**
     * 解析传入参数：inputs
     * @param $inputs
     * @return array
     */
    private function parseInputsParameter($inputs)
    {
        if (!$inputs) return false;

        $inputsArr = [];
        do {
            $inputs = substr($inputs, strpos($inputs, '#{') + 2);
            $inputs = substr($inputs, 0, strpos($inputs, ','));
            $inputsArr[] = [
                'parameter' => $inputs
            ];
        } while (strpos($inputs, '#{') !== false);

        return $inputsArr;
    }

    /**
     * 解析传入参数：outputs
     * @param $outputs
     * @return array
     */
    private function parseOutputsParameter($outputs)
    {
        if (!$outputs) return false;

        $outputsArr = [];
        $sqlSelect = new SqlSelect();
        $outputs = $sqlSelect->parseResultMapXml($outputs);
        foreach ($outputs as $output) {
            $outputsArr[] = [
                'resultMap' => $output['property']
            ];
        }

        return $outputsArr;
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

}

