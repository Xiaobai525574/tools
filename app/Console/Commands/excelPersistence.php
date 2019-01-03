<?php

namespace App\Console\Commands;

use App\Tables;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class excelPersistence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $tables = new Tables();
        $tables->truncate();
        foreach ($excelsPath as $excelPath) {
            $this->info('start parsing excel:' . $excelPath);

            $excel = IOFactory::load($excelPath);
            foreach ($excel->getWorksheetIterator() as $key => $sheet) {
                if ($key == 0) continue;
                $tables = new Tables();
                $tables->table_name = $sheet->getCell('H2')->getValue();
                $tables->sheet_name = $sheet->getTitle();
                $highestRow = $sheet->getHighestRow();
                $primaryKey = '';
                $uniqueKey = '';
                for ($i = 9; $i <= $highestRow; $i++) {
                    if ($sheet->getCell('L' . $i)->getValue() === '○') {
                        if ($primaryKey) $primaryKey .= ',';
                        $primaryKey .= $sheet->getCell('G' . $i)->getValue();
                    }
                    if ($sheet->getCell('N' . $i)->getValue() === '○') {
                        if ($uniqueKey) $uniqueKey .= ',';
                        $uniqueKey .= $sheet->getCell('G' . $i)->getValue();
                    }
                }
                $tables->primary_key = $primaryKey;
                $tables->unique_key = $uniqueKey;
                $tables->save();
            }
            unset($excel);

            $this->info('success!^-^');
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
        $dir = storage_path(config('tools.storage.tablesDefinitionPath'));
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
