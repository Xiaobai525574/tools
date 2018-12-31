<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:20
 */

namespace App\Http\Services\SqlCodeService;

use App\Http\Services\SqlExcelService\SqlExcel;

/**code is far away from bug with the animal protecting
 *  ┏┓　　　┏┓
 *┏┛┻━━━┛┻┓
 *┃　　　　　　　┃ 　
 *┃　　　━　　　┃
 *┃　┳┛　┗┳　┃
 *┃　　　　　　　┃
 *┃　　　┻　　　┃
 *┃　　　　　　　┃
 *┗━┓　　　┏━┛
 *　　┃　　　┃神兽保佑
 *　　┃　　　┃代码无BUG！
 *　　┃　　　┗━━━┓
 *　　┃　　　　　　　┣┓
 *　　┃　　　　　　　┏┛
 *　　┗┓┓┏━┳┓┏┛
 *　　　┃┫┫　┃┫┫
 *　　　┗┻┛　┗┻┛
 *　　　
 */

class SqlCode
{
    /**
     * 代码中的数据源
     * @var SqlExcel null
     */
    protected $excel = null;

    public function getExcel()
    {
        return $this->excel;
    }

    public function setExcel($excel)
    {
        $this->excel = $excel;
    }

    /**
     * 根据所给字段名集合，获取excel最后一行对应的值
     * @param $fields
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getValuesFromExcel($fields)
    {
        $excelField = null;
        $highestRow = null;
        $sqlExcel = $this->getExcel();
        foreach ($fields as &$field) {
            if (key_exists('tableName', $field) && !empty($field['tableName'])) {
                $sheet = SqlExcel::getSheetByActualNameStatic($field['tableName'], $sqlExcel);
                $highestRow = $sheet->getHighestRow();
                foreach ($sheet->getColumnIterator() as $columnIndex => $column) {
                    $excelField = $sheet->getCell($columnIndex . 1)->getValue();
                    if ($excelField == $field['name']) {
                        $field['value'] = $sheet->getCell($columnIndex . $highestRow)->getValue();
                    }
                }
            }
        }

        return $fields;
    }

}