<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/04
 * Time: 17:47
 */

namespace App\Http\Services;


use App\Http\Services\SqlExcelService\Iterator;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class sqlExcelService extends Spreadsheet
{

    public function __construct(Spreadsheet $spreadsheet = null)
    {
        parent::__construct();
        if ($spreadsheet) {
            $this->removeSheetByIndex(0);
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $this->addSheet($sheet);
            }
        }
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
            $tplExcelPath = $this->getSqlEXcelPath($name);
            /*解决表名过长问题*/
            $sqlSheet = $this->getSqlSheet($name);
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
     * 获取相应的绝对路径
     * @param $path
     * @return string
     */
    public static function getAPath($path)
    {
        return config('filesystems.disks.local.root') . $path;
    }

    public function getSqlSheetIterator()
    {
        $startSheet = 0;
        /*在循环中去除renametable页*/
        if ($this->sheetNameExists(config('tools.excel.renameTableTitle'))) $startSheet = 1;
        return new Iterator($this, $startSheet);
    }

    /**
     * 根据所给sheet页名称，获取真实表名
     * @param $title
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getActualName($title)
    {
        $renameTable = config('tools.excel.renameTableTitle');
        if ($this->sheetNameExists($renameTable)) {
            $sheet = $this->getSheetByName($renameTable);
            $highestRow = $sheet->getHighestRow();

            for ($i = 2; $i <= $highestRow; $i++) {
                $alias = $sheet->getCell('A' . $i)->getValue();
                if ($alias == $title) {
                    $title = $sheet->getCell('B' . $i)->getValue();
                }
            }
        }

        return $title;
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
     * 根据表名获取一个sheet页，并处理表名过长问题
     * @param $name
     * @return sqlSheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function getSqlSheet($name)
    {
        $alias = $name;
        if (strlen($name) > 31) {
            $alias = substr($name, 0, 31);
            $this->makeRenameTableSheet($alias, $name);
        }
        $sheet = new sqlSheet(null, $alias);

        return $sheet;
    }

    /**
     * 向表重命名页添加一条数据
     * @param $alias
     * @param $name
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function makeRenameTableSheet($alias, $name)
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
        $sheet->getCell('A' . $currentRow)->setValue($alias);
        $sheet->getCell('B' . $currentRow)->setValue($name);

        return true;
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
