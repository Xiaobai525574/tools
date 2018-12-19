<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/04
 * Time: 17:47
 */

namespace App\Http\Services;


use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class sqlExcelService extends Spreadsheet
{
    /*当前操作行*/
    private $currentRow = 2;

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
            if (!$name) continue;
            $tplExcelPath = $this->getSqlEXcelPath($name);
            $sqlSheet = new sqlSheet(null, $name);
            $this->addSheet($sqlSheet);
            if (Storage::disk('local')->exists($tplExcelPath)) {
                $tplSheet = IOFactory::load($this->getAPath($tplExcelPath))->getSheet(0);
                foreach ($tplSheet->getRowIterator() as $rowIndex => $row) {
                    foreach ($row->getCellIterator() as $columnIndex => $cell) {
                        $sqlSheet->setCellValue($columnIndex . $rowIndex, $cell->getValue())
                            ->duplicateStyle($cell->getStyle(), $columnIndex . $rowIndex);
                    }
                }
            }
        }
        if (!$this->getSheetCount()) $this->addSheet(new sqlSheet(null, 'sqlSheet'));

        return $this;
    }

    /**
     * 对每个sheet页添加数据
     * @param $quantities
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createData($quantities)
    {
        if (!$quantities) return $this;

        if (is_array($quantities)) {
            foreach ($this->getWorksheetIterator() as $key => $sheet) {
                $sheet->addSqlRows($quantities[$key] - 1);
            }
        } else {
            foreach ($this->getWorksheetIterator() as $key => $sheet) {
                $sheet->addSqlRows($quantities - 1);
            }
        }
        return $this;
    }

    /**
     * 唯一化数据
     * @return $this
     */
    public function uniqueSqlRows()
    {
        foreach ($this->getWorksheetIterator() as $sheet) {
            $sheet->uniqueSqlRows();
        }
        return $this;
    }

    /**
     * 将所给字段在表格中逐行依次标红，最后一行中全部标红
     * @param $redFields
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function redData($fields)
    {
        if (!$fields) return $this;

        foreach ($this->getWorksheetIterator() as $sheetIndex => $sheet) {
            $sheet->redData($fields[$sheetIndex]);
        }
        return $this;
    }

    /**
     * 将所给字段在表格中标橙（标红的行除外）
     * @param $fields
     * @param null $row
     * @return $this
     */
    public function orangeData($fields, $row = null)
    {
        if (!$fields) return $this;

        foreach ($this->getWorksheetIterator() as $sheetIndex => $sheet) {
            $sheet->orangeData($fields[$sheetIndex]);
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
    public static function getAPath($path)
    {
        return config('filesystems.disks.local.root') . $path;
    }

    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    public function setCurrentRow($currentRow)
    {
        $this->currentRow = $currentRow;
    }

}

/**
 *
 * ━━━━━━神兽出没━━━━━━
 * 　　　┏┓　　　┏┓
 * 　　┏┛┻━━━┛┻┓
 * 　　┃　　　　　　　┃
 * 　　┃　　　━　　　┃
 * 　　┃　┳┛　┗┳　┃
 * 　　┃　　　　　　　┃
 * 　　┃　　　┻　　　┃
 * 　　┃　　　　　　　┃
 * 　　┗━┓　　　┏━┛Code is far away from bug with the animal protecting
 * 　　　　┃　　　┃    神兽保佑,代码无bug
 * 　　　　┃　　　┃
 * 　　　　┃　　　┗━━━┓
 * 　　　　┃　　　　　　　┣┓
 * 　　　　┃　　　　　　　┏┛
 * 　　　　┗┓┓┏━┳┓┏┛
 * 　　　　　┃┫┫　┃┫┫
 * 　　　　　┗┻┛　┗┻┛
 *
 * ━━━━━━感觉萌萌哒━━━━━━
 */
