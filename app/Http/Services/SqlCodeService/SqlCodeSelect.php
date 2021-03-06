<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/31
 * Time: 8:56
 */

namespace App\Http\Services\SqlCodeService;

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

class SqlCodeSelect extends SqlCode
{

    /*select代码模板*/
    private $selectCode = <<<php
    /**
     * <b>[ケース観点]</b><br>
     * 1件を実行し、Repositoryクラスがエラーとならないこと<br>
     * 取得結果の各値の内容が正しいこと<br>
     *
     * 複数件取得可能な場合は1件を実行し、Repositoryクラスがエラーとならないこと<br>
     * 取得結果の各値の内容が正しいこと<br>
     *
     * 複数件取得可能な場合は複数件を実行し、Repositoryクラスがエラーとならないこと<br>
     * foreach：0件<br>
     * foreach：1件<br>
     * foreach：複数件<br>
     * ORDER BYにより並び順が指定されている場合、取得結果の並び順が正しいこと<br>
     * GROUP BYにより組み分けが指定されている場合、取得結果の組み分けが正しいこと<br>
     *
     * カウント件数取得<br>
     * <b>[想定結果]</b><br>
     * 1件取得<br>
     * 2件取得<br>
     * 3件取得<br>
     * カウント件数の1件取得<br>
     * @throws Exception
     **/
    @Test
    @DatabaseSetup(PATH + "setup_exec_id___num_.xlsx")
    public void exec_id___num_() throws Exception {

        // 入力値の設定
        _id_Input input = new _id_Input();
_input.set_
        // SQLの実行
        List<_id_Output> result = erm_subId_Repository.exec_id_(input, "0551");

        // 取得値の確認
        assertThat(result.size(), is(1));
        result.sort(Comparator.comparing(_id_Output::));
_assertions_
    }
php;

    public function getSelectCode()
    {
        return $this->selectCode;
    }

    /**
     * @param string $id ep:'PKTS0005', not 'execPKTS0005'
     * @param $num
     * @param $inputs
     * @param $outputs
     * @return mixed|string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function makeSelectCode($id, $num, $inputs, $outputs)
    {
        $code = $this->getSelectCode();
        /*替换id*/
        $code = str_replace('_id_', $id, $code);

        /*替换子id*/
        $code = str_replace('_subId_', substr($id, 0, 4), $code);

        /*替换num*/
        $code = str_replace('_num_', $num, $code);

        /*替换input.set*/
        $inputsStr = $this->makeInputsCode($inputs);
        $code = str_replace('_input.set_', $inputsStr, $code);

        /*替换assertThat*/
        $assertionsStr = $this->makeOutputsCode($outputs);
        $code = str_replace('_assertions_', $assertionsStr, $code);

        return $code;
    }

    /**
     * 生成inputs代码
     * @param $inputs
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function makeInputsCode($inputs)
    {
        if (!$inputs) return '';
        $inputs = $this->getValuesFromExcel($inputs);
        $inputsStr = '';
        foreach ($inputs as $key => $val) {
            $inputsStr .= '        input.set' . ucfirst($val['parameter']) . "(\"";
            if (key_exists('value', $val)) $inputsStr .= $val['value'];
            $inputsStr .= "\");\r\n";
        }

        return $inputsStr;
    }

    /**
     * 生成outputs代码
     * @param $outputs
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function makeOutputsCode($outputs)
    {
        if (!$outputs) return '';
        $outputs = $this->getValuesFromExcel($outputs);
        $outputsStr = '';
        foreach ($outputs as $key => $val) {
            if (key_exists('resultMap', $val)) {
                $outputsStr .= '        assertThat(result.get(0).get' . ucfirst($val['resultMap']) . "(), is(\"";
                if (key_exists('value', $val)) $outputsStr .= $val['value'];
                $outputsStr .= "\"));\r\n";
            }
        }

        return $outputsStr;
    }

}