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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class sqlExcelService extends Spreadsheet
{
    /**
     * 根据表名数组获取包含个表sheet页的Excel
     * @param $tplExcelNames
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getSqlExcel($tplExcelNames)
    {
        //Excel表格模板存放路径
        $this->removeSheetByIndex(0);
        foreach ($tplExcelNames as $key => $name) {
            $tplExcelPath = $this->getSqlEXcelPath($name);
            $sqlSheet = new Worksheet(null, $name);
            if (Storage::disk('local')->exists($tplExcelPath)) {
                $tplSheet = IOFactory::load($this->getAPath($tplExcelPath))->getSheet(0);
                $tplSheet = $tplSheet->toArray();
                $tplSheet = $this->sortFirstRow($tplSheet);
                $sqlSheet->fromArray($tplSheet);
            }
            $this->addSheet($sqlSheet);
        }
        return $this;
    }

    /**
     * 对每个sheet页添加数据
     * @param $quantities
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addData($quantities)
    {
        $sheets = $this->getAllSheets();
        foreach ($sheets as $key => $sheet) {
            $sheetArr = $sheet->toArray();
            is_array($quantities) ? $quantitie = $quantities[$key] : $quantitie = $quantities;
            for ($i = 1; $i < $quantitie; $i++) {
                $this->addRow($sheetArr);
            }
            $sheet->fromArray($sheetArr);
        }
        return $this;
    }

    /**
     * 将所给字段在表格中逐行依次标红，最后一行中全部标红
     * @param $redFields
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function redData($redFields)
    {
        $sharedStyle1 = new Style();

        $sharedStyle1->applyFromArray(
            [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => config('tools.color.red')]
                ]
            ]
        );
        $sheets = $this->getAllSheets();
        foreach ($sheets as $key => $sheet) {
            $sheetArr = $sheet->toArray();
            $row = 2;
            $redAll = $row + count($redFields);
            foreach ($sheetArr[0] as $key => $field) {
                if (in_array($field, $redFields)) {
                    $sheet->duplicateStyle($sharedStyle1, $this->decimalToABC($key) . $row);
                    $sheet->duplicateStyle($sharedStyle1, $this->decimalToABC($key) . $redAll);
                    $row++;
                }
            }
        }
        return $this;
    }

    /**
     * 保存sqlExcel到本地
     * @param $path
     * @return bool
     */
    public function saveSqlExcel($path)
    {
        $writer = IOFactory::createWriter($this, ucfirst(config('tools.excel.type')));
        $writer->save($this->getAPath($path));
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

    /**
     * 对sheet页数组的首行sql数据进行唯一性整理
     * @param $sheet
     * @return mixed
     */
    private function sortFirstRow($sheet)
    {
        $i = '0';
        $j = '0';
        if (key_exists(1, $sheet)) {
            foreach ($sheet[1] as $k => &$cell) {
                $length = mb_strlen($cell);
                if ($length > 1) {
                    $cell = $this->cellNumbered($cell, $i);
                    $i++;
                } elseif ($length == 1) {
                    $cell = $j;
                    $j = ($j >= 9) ? '0' : ($j + 1);
                }
            }
        }
        return $sheet;
    }

    /**
     * 添加一行数据
     * @param $sheetArr
     */
    private function addRow(&$sheetArr)
    {
        if (count($sheetArr) > 1) {
            $lastRow = end($sheetArr);
            $row = [];
            foreach ($lastRow as $key => $val) {
                $length = mb_strlen($val);
                if ($length > 1) {
                    $row[$key] = $this->cellNumbered($val);
                } elseif ($length == 1) {
                    $row[$key] = ($val >= 9) ? '0' : ($val + 1);
                }
            }
            if ($row) array_push($sheetArr, $row);
        }
    }

    /**
     * 十进制转字符串
     * @param $num
     * @return string
     */
    private function decimalToABC($num)
    {
        $ABCstr = '';
        $ten = $num;
        if ($ten == 0) return 'A';
        while ($ten != 0) {
            $x = $ten % 26;
            $ABCstr .= chr(65 + $x);
            $ten = intval($ten / 26);
        }

        return strrev($ABCstr);
    }

}