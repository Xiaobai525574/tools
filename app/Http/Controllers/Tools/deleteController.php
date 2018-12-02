<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PHPExcel_IOFactory;

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
        $excelName = $sql['table'] . '.xlsx';

        $excelPath = $this->exportExcel($excelName, count($sql['where']) + 1);

        if (!$excelPath) return redirect()->back()->with('table', 'notExists')->withInput();
        return Storage::download($excelPath);
    }

    /**
     * save Excel
     * @param $tableExcel
     * @param $excelName
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    protected function saveExcel($tableExcel, $excelName)
    {
        $excelWriter = PHPExcel_IOFactory::createWriter($tableExcel, 'Excel2007');
        $path = storage_path("app\\public\\delete\\") . $excelName;
        if (Storage::disk('local')->exists($path)) Storage::delete($path);

        $excelWriter->save($path);
    }
//
//    /**
//     * 生成Excel
//     * @param $excelName Excel名称
//     * @param $sql
//     * @return \PHPExcel
//     * @throws \PHPExcel_Exception
//     * @throws \PHPExcel_Reader_Exception
//     */
//    private function doExcel($excelName, $sql)
//    {
//        $tableExcel = PHPExcel_IOFactory::load(config('tools.tablesPath') . $excelName);
//        $sheet = $tableExcel->getSheet(0);
//        $sheetArray = $sheet->toArray(null, true, true, true);
//        $this->formatRow($sheetArray[2]);
//        $rowNum = count($sql['where']);
//        for ($i = 0; $i < $rowNum; $i++) {
//            $this->addRow($sheetArray);
//        }
//        $sheet->fromArray($sheetArray);
//        return $tableExcel;
//    }
//
//    /**
//     * save Excel
//     * @param $tableExcel
//     * @param $excelName
//     * @throws \PHPExcel_Reader_Exception
//     * @throws \PHPExcel_Writer_Exception
//     */
//    private function saveExcel($tableExcel, $excelName)
//    {
//        $excelWriter = PHPExcel_IOFactory::createWriter($tableExcel, 'Excel2007');
//        $excelWriter->save(config('tools.deleteExcelPath') . $excelName);
//    }
//
//    /**
//     * 格式化数据（实现每一个单元格数据的唯一性）
//     * @param $row
//     */
//    private function formatRow(&$row)
//    {
//        $i = 0;
//        $j = 0;
//        foreach ($row as $key => &$val) {
//            $length = mb_strlen($val);
//            if ($length > 1) {
//                $val = mb_substr($val, 0, $length - 2) . sprintf("%02d", $j);
//                $j++;
//            } elseif ($length == 1) {
//                $val = $i;
//                $i = ($i >= 9) ? '0' : ($i + 1);
//            }
//        }
//    }
//
//    /**
//     * 添加一行数据
//     * @param $sheetArray
//     */
//    private function addRow(&$sheetArray)
//    {
//        $lastRow = end($sheetArray);
//        foreach ($lastRow as $key => $val) {
//            $length = mb_strlen($val);
//            if ($length > 1) {
//                $row[$key] = mb_substr($val, 0, $length - 2)
//                    . sprintf("%02d", mb_substr($val, -2) + 1);
//            } elseif ($length == 1) {
//                $row[$key] = ($val >= 9) ? '0' : ($val + 1);
//            }
//        }
//        array_push($sheetArray, $row);
//    }
//
//    /**
//     * sql字符串转数组
//     * @param $sql
//     * @return mixed
//     */
//    private function sqlToArray($sql)
//    {
//        $sql = trim(strtolower($sql));
//        $sql = str_replace(array("\r\n", "\r", "\n"), " ", $sql);
//        $sql = preg_replace("/[\s]+/is"," ",$sql);
//
//        /*table*/
//        $sql = explode('from', $sql)[1];
//        $sql = explode('where', $sql);
//        $result['table'] = trim($sql[0]);
//
//        /*where*/
//        $wheres = explode('and', $sql[1]);
//        foreach ($wheres as &$value) {
//            $value = trim($value);
//            $result['where'][] = explode(' ', $value);
//        }
//
//        return $result;
//    }

}
