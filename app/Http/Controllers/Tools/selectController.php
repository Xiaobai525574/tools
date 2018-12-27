<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Services\CodeService;
use App\Http\Services\sqlExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class selectController extends Controller
{
    /**
     * select 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $a = <<<php
<resultMap type="jp.co.nttdata.erm.base.biz.sql1a.dto.rsqs.RSQS2035Output" id="RSQS2035Output">
        <result column="ikr_sqh_fil_knr_bng" property="ikkatsuKirokuSeikyuFileKanriNo"/>
        <result column="fil_id" property="fileID"/>
        <result column="krr_trk_cec_kbn" property="karitorokuKekkaKbnCd"/>
        <result column="ikr_sqh_sin_kbn" property="ikkatsuKirokuSeikyuShinseiKbnCd"/>
        <result column="fil_ukt_nji" property="fileUketsukeTime"/>
        <result column="a01_rsh_bng" property="seikyusyaInfRiyosyaNo"/>
        <result column="a01_hjnmei_cjnjgysha_mei" property="seikyusyaInfName"/>
        <result column="a01_hjnmei_cjnjgyshamei_cna" property="seikyusyaInfNameKana"/>
        <result column="a01_bsh_mei_tou" property="seikyusyaInfBusyoName"/>
        <result column="a01_bsh_meitou_cna" property="seikyusyaInfBusyoNameKana"/>
        <result column="a01_bnk_cod" property="seikyusyaInfBankCd"/>
        <result column="a01_bnk_mei" property="seikyusyaInfBankName"/>
        <result column="sqh_kns_sij_bun" property="seikyuKensuSeijo"/>
        <result column="skn_kgk_sij_bun" property="saikenKingakuSeijo"/>
        <result column="sub_fil_suu_sij_bun" property="subFileSeijo"/>
        <result column="sub_fil_suu_err_bun" property="subFileError"/>
        <result column="fil_mei" property="fileName"/>
        <result column="err_fil_id" property="errFileID"/>
        <result column="err_msg" property="errorMsg"/>
        <result column="oks_gwa_cen_kkn_cod" property="okyakusamagawaCenterKakuninCd"/>
        <result column="tst_flg" property="testFlg"/>
        <result column="tci_knr_bng" property="tsuchiKanriNo"/>
        <result column="tto_sha_smi" property="tantosyaShimei"/>
        <result column="tto_sha_cmt" property="tantosyaComment"/>
        <result column="krr_trk_iri_jii_nji" property="karitorokuIraiJitsushiTime"/>
        <result column="sun_iri_jii_jok_kbn" property="syoninIraiJitsushiJokyoKbnCd"/>
        <result column="hsy_mu0_juo_kof_umu_flg" property="hosyoNashiJotoKeikokuUmuFlg"/>
        <result column="trhsak_msy_hyj_umu_flg" property="torihikisakiNameHyojiUmuFlg"/>
        <result column="trhsak_knr_hit_sgy_acc_khi_flg" property="torihikisakiKanriHaitaSeigyoAccessKahiFlg"/>
        <result column="trhsak_mtr_kns" property="torihikisakiMitourokuKensu"/>
        <result column="fct_skn_snz_flg" property="factoringSaikenSonzaiFlg"/>
    </resultMap>
    <select id="execRSQS2035" parameterType="jp.co.nttdata.erm.base.biz.sql1a.dto.rsqs.RSQS2035Input" resultMap="RSQS2035Output">
        SELECT
                t1.ikr_sqh_fil_knr_bng
                ,t1.fil_id
                ,t1.krr_trk_cec_kbn
                ,t1.ikr_sqh_sin_kbn
                ,t1.fil_ukt_nji
                ,t1.a01_rsh_bng
                ,t1.a01_hjnmei_cjnjgysha_mei
                ,t1.a01_hjnmei_cjnjgyshamei_cna
                ,t1.a01_bsh_mei_tou
                ,t1.a01_bsh_meitou_cna
                ,t1.a01_bnk_cod
                ,t1.a01_bnk_mei
                ,t1.sqh_kns_sij_bun
                ,t1.skn_kgk_sij_bun
                ,t1.sub_fil_suu_sij_bun
                ,t1.sub_fil_suu_err_bun
                ,t1.fil_mei
                ,t1.err_fil_id
                ,t1.err_msg
                ,t1.oks_gwa_cen_kkn_cod
                ,t1.tst_flg
                ,t1.tci_knr_bng
                ,t1.tto_sha_smi
                ,t1.tto_sha_cmt
                ,t1.krr_trk_iri_jii_nji
                ,t1.sun_iri_jii_jok_kbn
                ,t1.hsy_mu0_juo_kof_umu_flg
                ,t1.trhsak_msy_hyj_umu_flg
                ,t1.trhsak_knr_hit_sgy_acc_khi_flg
                ,t1.trhsak_mtr_kns
                ,t1.fct_skn_snz_flg
            FROM
                ${featureSchema}.tr_not_dok_sqh_you_ikr_fil_knr t1
                ,${featureSchema}.tr_not_dok_sqh_you_ikr_sub_fil_knr t2
                ,${featureSchema}.te_ivttci_jho t3
            WHERE
                t2.bnk_cod = t3.bnk_cod
                AND t2.ikt_sqh_bng = t3.sqh_bng
                AND t1.bnk_cod = t2.bnk_cod
                AND t1.ikr_sqh_fil_knr_bng = t2.ikr_sqh_fil_knr_bng
                AND t3.tci_knr_bng = #{tsuchiKanriNo,jdbcType=CHAR}
                AND t3.bnk_cod = #{bankCd,jdbcType=CHAR}
                AND t3.skj_flg = #{sakujoFlg,jdbcType=NUMERIC}
                AND t2.skj_flg = #{sakujoFlg,jdbcType=NUMERIC}
                AND t1.skj_flg = #{sakujoFlg,jdbcType=NUMERIC}
            -- WITH OPTION LOCK_MODE(FL)
         -- RSQS2035
    </select>
php;

        return view('select/index');
    }

    /**
     * 创建excel并向浏览器提供下载
     * @param Request $request
     * @param sqlExcelService $sqlExcelService
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcel(Request $request, sqlExcelService $sqlExcelService)
    {
        //处理sql字符串
        $this->parseXml($request->input('xml'));

        //每张表需要生成的数据量
        $rows = $request->input('quantity');
        if (!$rows) $rows = count($this->getWhere()) + 2;
        //生成excel的保存路径
        $savePath = $this->getSavePath($this->getId(), $request->input('excelNum'));
        //需要标红的单元格数组
        $redFields = $this->getAllRedFields();
        //需要标橙的单元格数组
        $orangeFields = $this->getAllOrangeFields();

        $sqlExcelService = $sqlExcelService->getSqlExcel(array_column($this->getFrom(), 'name'));
        foreach ($sqlExcelService->getSqlSheetIterator() as $key => $sqlSheet) {
            $sheetTitle = $sqlExcelService->getActualName($sqlSheet->getTitle());
            $sqlSheet->addSqlRows($rows - 1)
                ->uniqueSqlRows()
                ->redData($redFields[$sheetTitle])
                ->orangeData($orangeFields[$sheetTitle])
                ->setSelectedCell('A1');
        }
        $sqlExcelService->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    /**
     * 通过参数创建excel
     * @param Request $request
     * @param sqlExcelService $sqlExcel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getExcelByParameters(Request $request, sqlExcelService $sqlExcel)
    {
        if ($request->method() == 'GET') return view('select/getExcelByTables');

        //生成excel的保存路径
        $savePath = $this->getSavePath($request->input('id'), $request->input('excelNum'));
        $rows = $request->input('tableRows');
        $whereFields = $request->input('tableWheres');
        $selectFields = $request->input('tableSelects');
        $sqlExcel->getSqlExcel($request->input('tableNames'));
        if ($sqlExcel->getSheetNames()[0] != 'sqlSheet') {
            foreach ($sqlExcel->getSqlSheetIterator() as $key => $sheet) {
                $sheet->addSqlRows($rows[$key] - 1)
                    ->uniqueSqlRows();

                if ($whereFields[$key]) {
                    if (strpos($whereFields[$key], '=') !== false) {
                        $whereFields[$key] = array_column(array_column($this->parseWhere($whereFields[$key]), 0), 1);
                    } else {
                        $whereFields[$key] = explode(',', str_replace(' ', '', $whereFields[$key]));
                    }
                    $sheet->redData($whereFields[$key]);
                }

                if ($selectFields[$key]) {
                    $selectFields[$key] = array_column($this->parseSelect($selectFields[$key]), 1);
                    $sheet->orangeData($selectFields[$key]);
                }

                $sheet->setSelectedCell('A1');
            }
            $sqlExcel->setActiveSheetIndex(0);
        }
        $sqlExcel->saveSqlExcel($savePath);

        return Storage::download($savePath);
    }

    /**
     * 获取代码模板
     * @param Request $request
     * @param CodeService $codeService
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getCode(Request $request, CodeService $codeService)
    {
        //处理sql字符串
        $this->parseXml($request->input('xml'));

        $num = $request->input('num');
        $inputs = $this->getCodeInputs($num);
        $outputs = $this->getCodeOutputs($num);
        $code = $codeService->makeSelectCode(substr($this->getId(), 4), $num, $inputs, $outputs);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    /**
     * 通过参数获取模板
     * @param Request $request
     * @param CodeService $codeService
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getCodeByParameters(Request $request, CodeService $codeService)
    {
        $num = $request->input('num');
        $id = $request->input('id');
        $inputs = $this->parseInputsParameter($request->input('inputs'));
        $resultMap = $this->parseResultMapXml($request->input('assertions'));
        $savePath = $this->getSavePath($id, $num);
        $assertions = $this->getLastRowValues($savePath, $resultMap, $inputs);
        $code = $codeService->makeSelectCode(substr($id, 4), $num, $inputs, $assertions);

        $result = [
            'status' => 'success',
            'info' => $code
        ];
        return $result;
    }

    /**
     * 获取代码输入字段集合
     * @param $num
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getCodeInputs($num)
    {
        $savePath = $this->getSavePath($this->getId(), $num);
        $fields = $this->toClassify($this->getWhereParameters());
        $result = $this->getLastRowValues($savePath, $fields);

        return $result;
    }

    /**
     * 获取代码输出字段集合
     * @param $num
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getCodeOutputs($num)
    {
        $savePath = $this->getSavePath($this->getId(), $num);
        $fields = $this->getSelect();
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) {
                $result[] = $fields[$key];
                unset($fields[$key]);
            }
        }
        $fields = $this->toClassify($fields);
        $result = array_merge($result, $this->getLastRowValues($savePath, $fields));

        return $result;
    }

    /**
     * 根据所给字段名集合，获取excel最后一行对应的值
     * @param $excelPath
     * @param $fields
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function getLastRowValues($excelPath, $fields)
    {
        if (Storage::disk('local')->exists($excelPath)) {
            $sqlExcel = IOFactory::load(sqlExcelService::getAPath($excelPath));
            $sqlExcel = new sqlExcelService($sqlExcel);
            $excelField = null;
            $highestRow = null;
            $result = [];

            foreach ($sqlExcel->getSqlSheetIterator() as $key => $sheet) {
                $sheetTitle = $sqlExcel->getActualName($sheet->getTitle());
                foreach ($sheet->getColumnIterator() as $columnIndex => $column) {
                    $excelField = $sheet->getCell($columnIndex . 1)->getValue();
                    foreach ($fields[$sheetTitle] as $key => $value) {
                        if ($excelField == $value['name']) {
                            $highestRow = $sheet->getHighestRow();
                            $value['value'] = $sheet->getCell($columnIndex . $highestRow)->getValue();
                            $result[] = $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 获取Excel保存路径
     * @param $id
     * @param $num
     * @return string
     */
    private function getSavePath($id, $num)
    {
        return config('tools.storage.selectPath') . 'setup_'
            . $id . '_' . $num . '.' . config('tools.excel.type');
    }

    /**
     * 解析传入的inputs参数
     * @param $inputs
     * @return array
     */
    private function parseInputsParameter($inputs)
    {
        $inputsArr = [];
        do {
            $inputs = substr($inputs, strpos($inputs, '#{') + 2);
            $inputsArr[] = substr($inputs, 0, strpos($inputs, ','));
        } while (strpos($inputs, '#{') !== false);

        return $inputsArr;
    }

}

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
