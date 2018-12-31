<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:19
 */

namespace App\Http\Services\SqlExcelService;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 *　　　　　　　　┏┓　　　┏┓+ +
 *　　　　　　　┏┛┻━━━┛┻┓ + +
 *　　　　　　　┃　　　　　　　┃ 　
 *　　　　　　　┃　　　━　　　┃ ++ + + +
 *　　　　　　 ████━████ ┃+
 *　　　　　　　┃　　　　　　　┃ +
 *　　　　　　　┃　　　┻　　　┃
 *　　　　　　　┃　　　　　　　┃ + +
 *　　　　　　　┗━┓　　　┏━┛
 *　　　　　　　　　┃　　　┃　　　　　　　　　　　
 *　　　　　　　　　┃　　　┃ + + + +
 *　　　　　　　　　┃　　　┃　　　　Code is far away from bug with the animal protecting　　　　　　　
 *　　　　　　　　　┃　　　┃ + 　　　　神兽保佑,代码无bug　　
 *　　　　　　　　　┃　　　┃
 *　　　　　　　　　┃　　　┃　　+　　　　　　　　　
 *　　　　　　　　　┃　 　　┗━━━┓ + +
 *　　　　　　　　　┃ 　　　　　　　┣┓
 *　　　　　　　　　┃ 　　　　　　　┏┛
 *　　　　　　　　　┗┓┓┏━┳┓┏┛ + + + +
 *　　　　　　　　　　┃┫┫　┃┫┫
 *　　　　　　　　　　┗┻┛　┗┻┛+ + + +
 */

class SqlSheet extends Worksheet
{
    /*为长度为1的字段和长度等于2的和长度大于2字段分别建立索引*/
    private $fieldsIndexes = [];

    /*当前操作行*/
    private $currentRow = 2;

    /**
     * 初始化列：列宽、数据标志位（长度为1的字段和长度等于2和长度大于2的字段用两个计数器去标志）
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function initSqlColumns()
    {
        if ($this->getCell('A2')->getFormattedValue() == '') return $this;
        $fieldsIndexes = [];
        $i = '0';
        $j = '10';
        $m = '100';
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $this->getColumnDimension($columnIndex)->setAutoSize(true);
            $len = strlen($this->getCell($columnIndex . 2)->getFormattedValue());
            if ($len == 1) {
                $fieldsIndexes[$columnIndex] = ['type' => 'character', 'index' => $i];
                $i = ($i >= 9) ? '0' : ((int)$i + 1);
            } elseif ($len == 2) {
                $fieldsIndexes[$columnIndex] = ['type' => 'character2', 'index' => $j];
                $j = ($j >= 99) ? '10' : ((int)$j + 1);
            } elseif ($len > 2) {
                $fieldsIndexes[$columnIndex] = ['type' => 'characters', 'index' => $m];
                $m = ($m >= 999) ? '100' : ((int)$m + 1);
            }
        }
        $this->setFieldsIndexes($fieldsIndexes);

        return $this;
    }

    /**
     * 添加$quantity行数据
     * @param $quantity
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSqlRows($quantity)
    {
        if (!$quantity || $quantity < 1) return $this;

        for ($i = 0; $i < $quantity; $i++) {
            $this->addSqlRow();
        }

        return $this;
    }

    /**
     * 添加一行数据
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSqlRow()
    {
        $highestRow = $this->getHighestRow();
        $highestColumn = Coordinate::columnIndexFromString($this->getHighestColumn());
        for ($column = 1; $column <= $highestColumn; $column++) {
            $this->setCellValueByColumnAndRow($column, $highestRow + 1
                , $this->getCellByColumnAndRow($column, $highestRow));
        }

        return $this;
    }

    /**
     * 唯一化数据
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function uniqueSqlRows()
    {
        if (!$this->getFieldsIndexes()) $this->initSqlColumns();
        foreach ($this->getRowIterator(config('tools.excel.startRow')) as $rowIndex => $row) {
            foreach ($row->getCellIterator() as $columnIndex => $cell) {
                $num = $this->getSqlCellIndex($columnIndex, $rowIndex);
                $this->setCellValueExplicit($columnIndex . $rowIndex
                    , $this->mergeStr($cell->getValue(), $num)
                    , DataType::TYPE_STRING)
                    ->duplicateStyle($this->getStyle($columnIndex . 2), $columnIndex . $rowIndex);
            }
        }

        return $this;
    }

    /**
     * 将所给字段依次标红，并将同列中其他单元格数据一致化
     * @param array $fields
     * @param null $row
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function redData($fields, $row = null)
    {
        if (!$fields) return $this;
        $fieldNames = array_unique(array_column($fields, 'name'));
        if (!$row) $row = $this->getCurrentRow();
        $highestRow = $row + count($fieldNames);
        $style = new Style();
        $style->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => config('tools.color.red')]
            ],
            'font' => [
                'name' => 'ＭＳ Ｐゴシック'
            ]
        ]);

        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $fieldName = $this->getCell($columnIndex . 1)->getValue();
            if (in_array($fieldName, $fieldNames)) {
                foreach ($column->getCellIterator($this->getCurrentRow()) as $rowIndex => $cell) {
                    if ($rowIndex == $row || $rowIndex == $highestRow) {
                        $this->duplicateStyle($style, $columnIndex . $rowIndex);
                    } else {
                        $this->setCellValueExplicit($columnIndex . $rowIndex
                            , $this->getCell($columnIndex . ($highestRow + 1))->getValue()
                            , DataType::TYPE_STRING);
                    }
                }
                $row++;
            }
        }

        $this->setCurrentRow($row + 1);
        return $this;
    }

    /**
     * 将所给字段在表格中标橙（标红的行除外）
     * @param $fields
     * @param null $row
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function orangeData($fields, $row = null)
    {
        if (!$fields) return $this;
        $fieldNames = array_unique(array_column($fields, 'name'));
        if (!$row) $row = $this->getCurrentRow();
        $highestRow = $this->getHighestRow();
        $style = new Style();
        $style->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => config('tools.color.orange')]
            ],
            'font' => [
                'name' => 'ＭＳ Ｐゴシック'
            ]
        ]);
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $fieldName = $this->getCell($columnIndex . 1)->getValue();
            if (in_array($fieldName, $fieldNames)) {
                $this->duplicateStyle($style, $columnIndex . $row . ':' . $columnIndex . $highestRow);
            }
        }
        $this->setCurrentRow($highestRow + 1);

        return $this;
    }

    /**
     * 合并两个字符串（将$num覆盖在$str的前三位或前两位或前一位）
     * @param $str
     * @param $num
     * @return string
     */
    private function mergeStr($str, $num)
    {
        $len = strlen($str);
        if ($len == 1) {
            $str = $num;
        } elseif ($len == 2) {
            $str = sprintf("%02d", $num) . mb_substr($str, 2);
        } elseif ($len > 2) {
            $str = sprintf("%03d", $num) . mb_substr($str, 3);
        }

        return $str;
    }

    /**
     * 获取某单元格的索引。根据单元格字符长度不同分别获取1位、2位、3位索引
     * @param $column
     * @param $row
     * @return bool|\Illuminate\Config\Repository|int|mixed
     */
    private function getSqlCellIndex($column, $row)
    {
        $fieldIndex = $this->getFieldsIndexes()[$column];
        $index = $fieldIndex['index'] + $row - config('tools.excel.startRow');
        if ($fieldIndex['type'] == 'character') {
            $index = $index % 10;
        } elseif ($fieldIndex['type'] == 'character2') {
            $index = $index % 100;
        } elseif ($fieldIndex['type'] == 'characters') {
            $index = $index % 1000;
        } else {
            return false;
        }

        return $index;
    }

    /**
     * 获取字段索引集合（该sheet页第一行单元格的数据）
     * @return array
     */
    public function getFieldsIndexes()
    {
        return $this->fieldsIndexes;
    }

    public function setFieldsIndexes($indexes)
    {
        $this->fieldsIndexes = $indexes;
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
