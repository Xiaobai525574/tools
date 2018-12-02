<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use PHPExcel_IOFactory;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $excelName
     * @param $rowNum
     * @return bool|string
     */
    public function exportExcel($excelName, $rowNum)
    {
        if (!Storage::disk('local')->exists('public\\tables\\' . $excelName)) return false;

        $tableExcel = $this->doExcel($excelName, $rowNum);
        $this->saveExcel($tableExcel, $excelName);
        return 'public\\delete\\' . $excelName;
    }

    /**
     * 生成Excel
     * @param $excelName
     * @param $rowNum
     * @return \PHPExcel
     */
    protected function doExcel($excelName, $rowNum)
    {
        $tableExcel = PHPExcel_IOFactory::load(config('tools.tablesPath') . $excelName);
        $sheet = $tableExcel->getSheet(0);
        $sheetArray = $sheet->toArray(null, true, true, true);
        $this->formatRow($sheetArray[2]);
        for ($i = 1; $i < $rowNum; $i++) {
            $this->addRow($sheetArray);
        }
        $sheet->fromArray($sheetArray);
        return $tableExcel;
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
        $path = storage_path("app\\public\\") . $excelName;
        if (Storage::disk('local')->exists($path)) Storage::delete($path);

        $excelWriter->save($path);
    }

    /**
     * 格式化数据（实现每一个单元格数据的唯一性）
     * @param $row
     */
    protected function formatRow(&$row)
    {
        $i = 0;
        $j = 0;
        foreach ($row as $key => &$val) {
            $length = mb_strlen($val);
            if ($length > 1) {
                $val = mb_substr($val, 0, $length - 2) . sprintf("%02d", $j);
                $j++;
            } elseif ($length == 1) {
                $val = $i;
                $i = ($i >= 9) ? '0' : ($i + 1);
            }
        }
    }

    /**
     * 添加一行数据
     * @param $sheetArray
     */
    protected function addRow(&$sheetArray)
    {
        $lastRow = end($sheetArray);
        $row = [];
        foreach ($lastRow as $key => $val) {
            $length = mb_strlen($val);
            if ($length > 1) {
                $row[$key] = mb_substr($val, 0, $length - 2)
                    . sprintf("%02d", mb_substr($val, -2) + 1);
            } elseif ($length == 1) {
                $row[$key] = ($val >= 9) ? '0' : ($val + 1);
            }
        }
        if ($row) array_push($sheetArray, $row);
    }

    /**
     * sql字符串转数组
     * @param $sql
     * @return mixed
     */
    protected function sqlToArray($sql)
    {
        $sql = trim(strtolower($sql));
        $sql = str_replace(array("\r\n", "\r", "\n"), " ", $sql);
        $sql = preg_replace("/[\s]+/is", " ", $sql);

        /*table*/
        $sql = explode('from', $sql)[1];
        $sql = explode('where', $sql);
        $result['table'] = trim($sql[0]);

        /*where*/
        $wheres = explode('and', $sql[1]);
        foreach ($wheres as &$value) {
            $value = trim($value);
            $result['where'][] = explode(' ', $value);
        }

        return $result;
    }
}
