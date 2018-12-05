<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/04
 * Time: 17:47
 */

namespace App\Http\Services;


use PhpOffice\PhpSpreadsheet\Spreadsheet;

class sqlExcelService extends Spreadsheet
{
    public function __construct()
    {
        echo 233;
    }

    public function addData()
    {
        return $this;
    }

    public function colorData()
    {
        return $this;
    }

    public function saveSqlExcel()
    {
        return 233;
    }

}