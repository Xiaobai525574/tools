<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/04
 * Time: 17:47
 */

namespace App\Http\Services;


use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class sqlExcelService extends Spreadsheet
{
    /**
     * 根据表名数组获取包含个表sheet页的Excel
     * @return $this
     */
    public function getSqlExcel($sqlExcelNames)
    {
        //Excel表格模板存放路径
        $this->removeSheetByIndex(0);
        foreach ($sqlExcelNames as $key => $name) {
            $sqlExcelPath = $this->getSqlEXcelPath($name);
            if (Storage::disk('local')->exists($sqlExcelPath)) {
                $sqlSheet = IOFactory::load($this->getAPath($sqlExcelPath))->getSheet(0);
            } else {
                $sqlSheet = new Worksheet(null, $name);
            }
            $this->addSheet($sqlSheet, $key);
        }
        return $this;
    }

    /**
     * 对每个sheet页的首行数据进行唯一性整理
     * @return $this
     */
    public function sortFirstRow()
    {
        $i = 0;
        $j = 0;
        $sheets = $this->getAllSheets();
        foreach ($sheets as $key => $sheet) {
            $sheetArr = $sheet->toArray();
            foreach ($sheetArr[1] as $k => &$cell) {
                $length = mb_strlen($cell);
                if ($length > 1) {
                    $cell = $this->cellNumbered($cell, $i);
                    $i++;
                } elseif ($length == 1) {
                    $cell = $j;
                    $i = ($j >= 9) ? '0' : ($j + 1);
                }
            }
            $sheet->fromArray($sheetArr);
        }
        return $this;
    }

    public function addData()
    {

        return $this;
    }

    public function colorData()
    {
        return $this;
    }

    /**
     * 保存sqlExcel到本地
     * @param $path
     * @return bool
     */
    public function saveSqlExcel($path)
    {
        IOFactory::createWriter($this, ucfirst(config('tools.excel.type')))->save($this->getAPath($path));
        return true;
    }

    /**
     * 获取sqlExcel模板存放路径
     * @param $name
     * @return string
     */
    private function getSqlExcelPath($name)
    {
        return config('tools.storage.tablesPath') . $name . '.' . config('tools.excel.type');
    }

    /**
     * 获取相应的绝对路径
     * @param $path
     * @return string
     */
    private function getAPath($path)
    {
        return config('filesystems.disks.local.root') . $path;
    }

    /**
     * 单元格数据进行编码，确保唯一性
     * @param $cellVal
     * @param bool $cellNum
     * @return string
     */
    private function cellNumbered($cellVal, $cellNum = false)
    {
        if ($cellNum === false) $cellNum = mb_substr($cellVal, 0, 2) + 1;
        return sprintf("%02d", $cellNum) . mb_substr($cellVal, 2);
    }

}