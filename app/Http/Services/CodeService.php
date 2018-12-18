<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/17
 * Time: 14:24
 */

namespace App\Http\Services;


class CodeService
{

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
     *
     * ORDER BYにより並び順が指定されている場合、取得結果の並び順が正しいこと<br>
     *
     * GROUP BYにより組み分けが指定されている場合、取得結果の組み分けが正しいこと<br>
     *
     * カウント件数取得<br>
     *
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
        List<_id_Output> result = ermPKTSRepository.exec_id_(input, "0551");

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
     * @param $assertions
     * @return mixed|string
     */
    public function makeSelectCode($id, $num, $inputs, $assertions)
    {
        $code = $this->getSelectCode();
        /*替换id*/
        $code = str_replace('_id_', $id, $code);

        /*替换num*/
        $code = str_replace('_num_', $num, $code);

        /*替换input.set*/
        $inputs = array_unique($inputs);
        $inputsStr = '';
        foreach ($inputs as $key => $val) {
            $inputsStr .= '        input.set' . ucfirst($val) . "();\r\n";
        }
        $code = str_replace('_input.set_', $inputsStr, $code);

        /*替换assertThat*/
        $assertions = array_unique($assertions);
        $assertionsStr = '';
        foreach ($assertions as $key => $assertion) {
            $assertionsStr .= '        assertThat(result.get(0).get' . ucfirst($assertion) . "(), is(\"\"));\r\n";
        }
        $code = str_replace('_assertions_', $assertionsStr, $code);

        return $code;
    }

}