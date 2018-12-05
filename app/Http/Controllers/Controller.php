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

    /*id例如：execPKTS0005*/
    private $id = null;

    /*表名*/
    private $table = null;

    /*where条件数组*/
    private $wheres = null;

    /**
     * @param $savePath
     * @param $tablePath
     * @param $rowNum
     * @return bool
     */
    public function exportExcel($savePath, $tablePath, $rowNum)
    {
        if (!Storage::disk('local')->exists($tablePath)) return false;
        $tableExcel = $this->doExcel(config('filesystems.disks.local.root') . $tablePath, $rowNum);

        return $this->saveExcel($tableExcel, $savePath);
    }

    /**
     * 生成Excel
     * @param $tableExcelAP String sql表的绝对路径
     * @param $rowNum
     * @return \PHPExcel
     */
    protected function doExcel($tableExcelAP, $rowNum)
    {
        $tableExcel = PHPExcel_IOFactory::load($tableExcelAP);
        /*删除多余sheet页*/
        $count = $tableExcel->getSheetCount();
        for ($i = 1; $i < $count; $i++) {
            $tableExcel->removeSheetByIndex(1);
        }
        /*添加数据*/
        $sheet = $tableExcel->getSheet(0);
        $sheetArray = $sheet->toArray(null, true, true, true);
        $this->formatRow($sheetArray[2]);
        for ($i = 1; $i < $rowNum; $i++) {
            $this->addRow($sheetArray);
        }
        $sheet->fromArray($sheetArray);
        /*格式化数据*/
        $countRow = count($sheetArray);
        for ($i = 3; $i <= $countRow; $i++) {
            foreach ($sheetArray[$i] as $key => $row) {
                $style = $sheet->getCell($key . '2')->getStyle();
                $sheet->setSharedStyle($style, $key . $i);
            }
        }

        return $tableExcel;
    }

    protected function colorExcel($tableExcel, $pValue)
    {
        $tableExcel;
    }

    /**
     * save Excel
     * @param $tableExcel
     * @param $savePath
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    protected function saveExcel($tableExcel, $savePath)
    {
        $excelWriter = PHPExcel_IOFactory::createWriter($tableExcel, 'Excel2007');
        if (Storage::disk('local')->exists($savePath)) Storage::delete($savePath);
        $excelWriter->save(config('filesystems.disks.local.root') . $savePath);

        return true;
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
                $val = $this->cellNumbered($val, $j);
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
                $row[$key] = $this->cellNumbered($val);
            } elseif ($length == 1) {
                $row[$key] = ($val >= 9) ? '0' : ($val + 1);
            }
        }
        if ($row) array_push($sheetArray, $row);
    }

    /**
     * 单元格数据进行编码，确保唯一性
     * @param $cellVal
     * @param bool $cellNum
     * @return string
     */
    protected function cellNumbered($cellVal, $cellNum = false)
    {
        if ($cellNum === false) $cellNum = mb_substr($cellVal, 0, 2) + 1;
        return sprintf("%02d", $cellNum) . mb_substr($cellVal, 2);
    }

    /**
     * sql字符串转数组
     * @param $sql
     * @return mixed
     */
    protected function setSql($sql)
    {
        /*去除换行符*/
        $sql = str_replace(array("\r\n", "\r", "\n"), " ", $sql);
        /*去除多余空格*/
        $sql = preg_replace("/[\s]+/is", " ", $sql);

        /*获取xml标签里的id*/
        $result['id'] = trim(explode('"', explode('>', $sql)[0])[1]);
        $this->setId($result['id']);

        /*获取sql字符串，并转为小写、去除xml标签、去除sql注释*/
        $sql = explode('--', strip_tags(strtolower($sql)))[0];

        /*table*/
        $sql = explode('where', explode('from', $sql)[1]);
        $result['table'] = trim(explode('.', $sql[0])[1]);
        $this->setTable($result['table']);
        $sql = $sql[1];

        /*where*/
        $wheres = explode('and', $sql);
        foreach ($wheres as $value) {
            $value = trim($value);
            $result['wheres'][] = explode(' ', $value);
        }
        $this->setWheres($result['wheres']);

        return $result;
    }

    protected function getSql()
    {
        return [
            'id' => $this->getId(),
            'table' => $this->getTable(),
            'wheres' => $this->getWheres()
        ];
    }

    protected function getId()
    {
        return $this->id;
    }

    protected function setId($id)
    {
        $this->id = $id;
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function setTable($table)
    {
        $this->table = $table;
    }

    protected function getWheres()
    {
        return $this->wheres;
    }

    protected function setWheres($wheres)
    {
        $this->wheres = $wheres;
    }
}
