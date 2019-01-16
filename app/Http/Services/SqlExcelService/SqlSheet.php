<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:19
 */

namespace App\Http\Services\SqlExcelService;

use App\Tables;
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
     * 真实表名
     * @var string
     */
    public $actualName = '';

    /**
     * SqlSheet constructor.
     * @param SqlExcel|null $parent
     * @param string $pTitle
     * @param null $actualName
     */
    public function __construct(SqlExcel $parent = null, $pTitle = 'SqlSheet', $actualName = null)
    {
        parent::__construct($parent, $pTitle);

        if (!$actualName) $actualName = $pTitle;
        $this->setActualName($actualName);
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

    public function getActualName()
    {
        return $this->actualName;
    }

    public function setActualName($actualName)
    {
        $this->actualName = $actualName;
    }

    /**
     * 初始化列：列宽、数据标志位（长度为1的字段和长度等于2和长度大于2的字段用两个计数器去标志）
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function initSqlFields()
    {
        if ($this->getCell('A2')->getFormattedValue() == '') return $this;
        /*获取该表的主键、唯一约束*/
        $tables = new Tables();
        $keys = $tables->getKeys($this->getActualName());
        /*主键样式：填充色标绿*/
        $primaryStyle = new Style();
        $primaryStyle->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => config('tools.color.green')]
            ],
            'font' => [
                'name' => 'ＭＳ Ｐゴシック'
            ]
        ]);
        /*唯一约束样式：字体颜色标红*/
        $uniqueStyle = new Style();
        $uniqueArr = [
            'font' => [
                'name' => 'ＭＳ Ｐゴシック',
                'color' => ['argb' => config('tools.color.red')]
            ]
        ];

        $i = '0';
        $j = '10';
        $m = '100';
        $n = '1000';
        $d = 0;//8位日期计数器
        $s = 0;//17位日期计数器
        $fieldsIndexes = [];
        $date = date('YmdHi', time());
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            /*列宽*/
            $this->getColumnDimension($columnIndex)->setAutoSize(true);
            /*标识主键*/
            $field = $this->getCell($columnIndex . '1');
            foreach ($keys['primary_key'] as $key) {
                if ($field->getValue() === $key) $this->duplicateStyle($primaryStyle, $columnIndex . '1');
            }
            /*标识唯一约束*/
            foreach ($keys['unique_key'] as $key) {
                if ($field->getValue() === $key) {
                    /*如果主键、唯一约束重合，既标填充色，也标字体颜色*/
                    $color = $field->getStyle()->getFill()->getStartColor()->getARGB();
                    if ($color === config('tools.color.green')) {
                        $uniqueArr['fill'] = [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['argb' => config('tools.color.green')]
                        ];
                    }

                    $uniqueStyle->applyFromArray($uniqueArr);
                    $this->duplicateStyle($uniqueStyle, $columnIndex . '1');
                }
            }
            /*数据标志位*/
            $cellValue = $this->getCell($columnIndex . 2)->getFormattedValue();
            $len = strlen($cellValue);
            switch ($len) {
                case 1:
                    $fieldsIndexes[$columnIndex] = ['type' => 'character', 'index' => $i];
                    $i = ($i >= 9) ? '0' : ((int)$i + 1);
                    break;
                case 2:
                    $fieldsIndexes[$columnIndex] = ['type' => 'character2', 'index' => $j];
                    $j = ($j >= 99) ? '10' : ((int)$j + 1);
                    break;
                case 3:
                    $fieldsIndexes[$columnIndex] = ['type' => 'character3', 'index' => $m];
                    $m = ($m >= 999) ? '100' : ((int)$m + 1);
                    break;
                case 8:
                    if (strtotime($cellValue)) {
                        $fieldsIndexes[$columnIndex] = [
                            'type' => 'date8',
                            'index' => date('Ymd', strtotime('now +' . $d . ' day'))
                        ];
                        $d++;
                        break;
                    }
                case 17:
                    if (strtotime(mb_substr($cellValue, 0, -3))) {
                        $fieldsIndexes[$columnIndex] = [
                            'type' => 'date17',
                            'index' => $date . sprintf("%05d", $s)
                        ];
                        $s++;
                        break;
                    }
                default:
                    $fieldsIndexes[$columnIndex] = ['type' => 'characters', 'index' => $n];
                    $n = ($n >= 9999) ? '1000' : ((int)$n + 1);
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
        foreach ($this->getColumnIterator() as $columnIndex => $column) {
            $cellValue = $this->getCell($columnIndex . $highestRow)->getValue();
            $this->setCellValueExplicit($columnIndex . ($highestRow + 1)
                , $cellValue, DataType::TYPE_STRING);
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
        if (!$this->getFieldsIndexes()) $this->initSqlFields();
        foreach ($this->getRowIterator(config('tools.excel.startRow')) as $rowIndex => $row) {
            foreach ($row->getCellIterator() as $columnIndex => $cell) {
                $index = $this->getSqlCellIndex($columnIndex, $rowIndex);
                $this->setCellValueExplicit($columnIndex . $rowIndex
                    , $this->mergeStr($cell->getValue(), $index)
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
                'color' => ['argb' => config('tools.color.red')]
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
                'color' => ['argb' => config('tools.color.orange')]
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
     * 合并两个字符串（将$index覆盖在$str的前三位或前两位或前一位）
     * @param $str
     * @param $index
     * @return string
     */
    private function mergeStr($str, $index)
    {
        $len = strlen($str);
        switch ($len) {
            case 1:
                $str = $index;
                break;
            case 2:
                $str = sprintf("%02d", $index) . mb_substr($str, 2);
                break;
            case 3:
                $str = sprintf("%03d", $index) . mb_substr($str, 3);
                break;
            case 8:
            case 17:
                $str = $index;
                break;
            default:
                $str = sprintf("%04d", $index) . mb_substr($str, 4);
        }

        return (string)$str;
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
        $add = $row - config('tools.excel.startRow');
        $index = $fieldIndex['index'] + $add;
        switch ($fieldIndex['type']) {
            case 'character':
                $index = $index % 10;
                break;
            case 'character2':
                $index = $index % 100;
                $index = $index < 10 ? '1' . $index : $index;
                break;
            case 'character3':
                $index = $index % 1000;
                $index = $index < 100 ? '1' . sprintf("%02d", $index) : $index;
                break;
            case 'characters':
                $index = $index % 10000;
                $index = $index < 1000 ? '1' . sprintf("%03d", $index) : $index;
                break;
            case 'date8':
                $index = date('Ymd', strtotime($fieldIndex['index'] . ' +' . $add . ' day'));
                break;
            case 'date17':
                $index = gmp_add($fieldIndex['index'], $add);
                break;
            default:
                return false;
        }

        return (string)$index;
    }

}
