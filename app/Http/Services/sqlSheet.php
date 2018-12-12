<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/07
 * Time: 8:37
 */

namespace App\Http\Services;


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class sqlSheet extends Worksheet
{
    /*为长度为1的字段和长度大于1的字段分别建立索引*/
    private $fieldsIndexes = [];

    /*当前操作行*/
    private $currentRow = 2;

    /**
     * 初始化列：列宽、数据标志位（长度为1的字段和长度大于1的字段用两个计数器去标志）
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function initColumns()
    {
        if (!$this->cellExistsByColumnAndRow(1, 2)) return $this;
        $fieldsIndexes = [];
        $i = '0';
        $j = '0';
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            /*列宽自适应不好用，辣鸡*/
            //$this->getColumnDimension($columnIndex)->setAutoSize(true);
            $len = strlen($this->getCell($columnIndex . 2)->getFormattedValue());
            if ($len > 1) {
                $fieldsIndexes[$columnIndex] = ['characters', $i];
                $i++;
            } elseif ($len == 1) {
                $fieldsIndexes[$columnIndex] = ['character', $j];
                $j = ($j >= 9) ? '0' : ((int)$j + 1);
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
    public function addRows($quantity)
    {
        if (!$quantity || $quantity < 1) return $this;

        for ($i = 0; $i < $quantity; $i++) {
            $this->addRow();
        }

        return $this;
    }

    /**
     * 添加一行数据
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addRow()
    {
        $highestRow = $this->getHighestRow();
        $highesColumn = Coordinate::columnIndexFromString($this->getHighestColumn());
        for ($column = 1; $column <= $highesColumn; $column++) {
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
    public function uniqueRows()
    {
        if (!$this->getFieldsIndexes()) $this->initColumns();
        foreach ($this->getRowIterator(config('tools.excel.startRow')) as $rowIndex => $row) {
            foreach ($row->getCellIterator() as $columnIndex => $cell) {
                $fieldIndex = $this->getFieldsIndexes()[$columnIndex];
                $num = $fieldIndex[1] + $rowIndex - config('tools.excel.startRow');
                if ($fieldIndex[0] == 'character') $num = $num % 10;
                $this->setCellValue($columnIndex . $rowIndex, $this->mergeStr($cell->getValue(), $num))
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
        if (!$row) $row = $this->getCurrentRow();
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

        $highestRow = $row + count($fields);
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $fieldName = $this->getCell($columnIndex . 1)->getValue();
            if (in_array($fieldName, $fields)) {
                foreach ($column->getCellIterator($this->getCurrentRow()) as $rowIndex => $cell) {
                    if ($rowIndex == $row || $rowIndex == $highestRow) {
                        $this->duplicateStyle($style, $columnIndex . $rowIndex);
                    } else {
                        $this->setCellValue($columnIndex . $rowIndex
                            , $this->getCell($columnIndex . ($highestRow + 1))->getValue());
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
        if (!$row) $row = $this->getCurrentRow();
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
        $highestRow = $this->getHighestRow();
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $fieldName = $this->getCell($columnIndex . 1)->getValue();
            if (in_array($fieldName, $fields)) {
                $this->duplicateStyle($style, $columnIndex . $row . ':' . $columnIndex . $highestRow);
            }
        }
        $this->setCurrentRow($highestRow + 1);

        return $this;
    }

    /**
     * 合并两个字符串（将$num覆盖在$str的前两位）
     * @param $str
     * @param $num
     * @return string
     */
    private function mergeStr($str, $num)
    {
        if (strlen($str) > 1) {
            $str = sprintf("%02d", $num) . mb_substr($str, 2);
        } elseif (strlen($str) == 1) {
            $str = $num;
        }

        return $str;
    }

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
