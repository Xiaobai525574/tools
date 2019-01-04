<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:19
 */

namespace App\Http\Services\SqlExcelService;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
class SqlExcel extends Spreadsheet
{

    /**
     * 静态方法：通过真实表名，获取对应的sheet页
     * @param $actualName
     * @param Spreadsheet $excel
     * @return Worksheet mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getSheetByActualNameStatic($actualName, $excel)
    {
        $sheetName = SqlExcel::getSheetNameStatic($actualName, $excel);
        return $excel->getSheetByName($sheetName);
    }

    /**
     * 静态方法：根据所给真实表名，获取sheet页名称
     * @param $actualName
     * @param Spreadsheet $excel
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getSheetNameStatic($actualName, $excel)
    {
        $sheetName = $actualName;
        $renameTable = config('tools.excel.renameTableTitle');
        if ($excel->sheetNameExists($renameTable)) {
            $sheet = $excel->getSheetByName($renameTable);
            $highestRow = $sheet->getHighestRow();

            for ($i = 2; $i <= $highestRow; $i++) {
                $value = $sheet->getCell('B' . $i)->getValue();
                if ($value == $actualName) {
                    $sheetName = $sheet->getCell('A' . $i)->getValue();
                }
            }
        }

        return $sheetName;
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

    /**
     * 获取sqlExcel模板存放路径
     * @param $name
     * @return string
     */
    public static function getTplExcelPath($name)
    {
        return config('tools.storage.tablesPath') . $name . '.' . config('tools.excel.type');
    }

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
            $tplExcelPath = $this->getTplExcelPath($name);
            /*解决表名过长问题*/
            $sqlSheet = $this->makeSqlSheet($name);
            $this->addSheet($sqlSheet);
            if (Storage::disk('local')->exists($tplExcelPath)) {
                $tplSheet = IOFactory::load($this->getAPath($tplExcelPath))->getSheet(0);
                foreach ($tplSheet->getRowIterator() as $rowIndex => $row) {
                    foreach ($row->getCellIterator() as $columnIndex => $cell) {
                        $sqlSheet->setCellValue($columnIndex . $rowIndex, $cell->getValue());
                    }
                }
            }
        }
        if (!$this->getSheetCount()) $this->addSheet(new sqlSheet());

        return $this;
    }

    /**
     * 保存sqlExcel到本地
     * @param $path
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveSqlExcel($path)
    {
        $writer = IOFactory::createWriter($this, ucfirst(config('tools.excel.type')));
        $writer->save($this->getAPath($path));
        return true;
    }

    /**
     * 通过真实表名，获取对应的sheet页
     * @param $actualName
     * @return Worksheet|null
     */
    public function getSheetByActualName($actualName)
    {
        foreach ($this->getWorksheetIterator() as $sheet) {
            if ($sheet->getActualName() === $actualName) {
                return $sheet;
            }
        }

        return null;
    }

    /**
     * 根据表名获取一个sheet页，并处理表名过长问题
     * @param $actualName
     * @return sqlSheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function makeSqlSheet($actualName)
    {
        $sheetName = $actualName;
        if (strlen($actualName) > 31) {
            $sheetName = substr($actualName, 0, 31);
            $this->makeRenameTableSheet($sheetName, $actualName);
        }
        $sheet = new sqlSheet(null, $sheetName, $actualName);

        return $sheet;
    }

    /**
     * 向表重命名页添加一条数据
     * @param $sheetName
     * @param $actualName
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function makeRenameTableSheet($sheetName, $actualName)
    {
        $title = config('tools.excel.renameTableTitle');
        if (!$this->sheetNameExists($title)) {
            $sheet = new sqlSheet(null, $title);
            $sheet->getCell('A1')->setValue('SHEET_NAME');
            $sheet->getCell('B1')->setValue('ACTUAL_NAME');
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $this->addSheet($sheet, 0);
        }

        $sheet = $this->getSheetByName($title);
        $currentRow = $sheet->getHighestRow() + 1;
        $sheet->getCell('A' . $currentRow)->setValue($sheetName);
        $sheet->getCell('B' . $currentRow)->setValue($actualName);

        return true;
    }

}