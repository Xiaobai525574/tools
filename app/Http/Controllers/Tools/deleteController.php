<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\sqlExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class deleteController extends Controller
{

    /**
     * delete首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('delete/index');
    }

    /**
     * 创建Excel
     * @param Request $request
     * @param sqlExcelService $sqlExcel
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcel(Request $request, sqlExcelService $sqlExcel)
    {
        //处理sql字符串
        $this->parseSqlXml($request->input('sql'));
        //生成excel的保存路径
        $savePath = config('tools.storage.deletePath') . 'setup_' . $this->getId() . '_001.xlsx';
        //每张表需要生成的数据量
        $quantity = $request->input('quantity') or $quantity = count($this->getWhere()) + 2;
        //需要标红的单元格数组
        $fields = array_column($this->getWhere(), 0);

        $sqlExcel->getSqlExcel(array_column($this->getFrom(), 0));
        $sqlExcel->getSheet(0)
            ->addRows($quantity - 1)
            ->uniqueRows()
            ->redData($fields)
            ->setSelectedCell('A1');
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    public function getCode(Request $request)
    {
        return 233;
    }

}

/**
 *
 *                       _oo0oo_
 *                      o8888888o
 *                      88" . "88
 *                      (| -_- |)
 *                      0\  =  /0
 *                    ___/`---'\___
 *                  .' \\|     |* '.
 *                 / \\|||  :  |||* \
 *                / _||||| -:- |||||- \
 *               |   | \\\  -  /// |   |
 *               | \_|  ''\---/''  |_/ |
 *               \  .-\__  '-'  ___/-. /
 *             ___'. .'  /--.--\  `. .'___
 *          ."" '<  `.___\_<|>_/___.' >' "".
 *         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *         \  \ `_.   \_ __\ /__ _/   .-` /  /
 *     =====`-.____`.___ \_____/___.-`___.-'=====
 *                       `=---='
 *
 *
 *     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *               佛祖保佑         永无BUG
 *
 */
