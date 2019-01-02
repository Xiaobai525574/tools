<?php

namespace App\Console\Commands;

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
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle()
    {
        $excelsPath = $this->getFileNames();
        foreach ($excelsPath as $excelPath) {
            $this->info($excelPath);
            $excel = IOFactory::load($excelPath);
            if ($excel) {
                $this->info('233');
            }
            unset($excel);
        }

        $this->info('success!^-^');
    }

    public function getFileNames()
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
