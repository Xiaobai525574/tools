<?php

namespace App\Console\Commands;

use App\Http\Services\SqlExcelService\SqlExcel;
use App\Http\Services\SqlExcelService\SqlSheet;
use App\Tables;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class lengthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:lengthCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check length';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle()
    {
        $excelsPath = $this->getFilesPath();
        $renameTableTitle = config('tools.excel.renameTableTitle');
        $tablesDir = SqlExcel::getAPath(config('tools.storage.tablesPath'));
        foreach ($excelsPath as $excelPath) {
            $this->info('checking excel:' . $excelPath);
            $excel = IOFactory::load($excelPath);
            foreach ($excel->getWorksheetIterator() as $key => $sheet) {
                $actualName = SqlExcel::getActualNameStatic($sheet->getTitle(), $excel);
                $this->info('    checking table:' . $actualName);
                if ($renameTableTitle == $actualName) continue;
                $tableExcel = IOFactory::load($tablesDir . $actualName . '.' . config('tools.excel.type'));
                $tableSheet = $tableExcel->getSheet(0);
                foreach ($tableSheet->getColumnIterator() as $columnIndex => $column) {
                    $tableValue = $tableSheet->getCell($columnIndex . '2')->getValue();
                    $tableLen = mb_strlen($tableValue);
                    $cellValue = $sheet->getCell($columnIndex . '2')->getValue();
                    $cellLen = mb_strlen($cellValue);
                    if ($tableLen != $cellLen) {
                        $field = $sheet->getCell($columnIndex . '1')->getValue();
                        $this->info('===false:' . $field . ', ' . $tableLen . '=>' . $cellLen);
                    }
                }
            }
            unset($excel);
        }
        $this->info('completed!^-^');
    }

    /**
     * 获取excel路径集合
     * @return array
     */
    public function getFilesPath()
    {
        //取得当前文件所在目录
        $dir = storage_path(config('tools.storage.checkExcels'));
        //判断目标目录是否是文件夹
        $file_arr = array();
        if (is_dir($dir)) {
            //打开
            if ($dh = @opendir($dir)) {
                //读取
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $file_arr[] = $dir . $file;
                    }
                }
                //关闭
                closedir($dh);
            }
        }
        return $file_arr;
    }
}
