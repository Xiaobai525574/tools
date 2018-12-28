<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:18
 */

namespace App\Http\Services\SqlService;


class Sql
{
    /*id例如：execPKTS0005*/
    private $id = null;

    /*表名*/
    private $from = [];

    /*where条件数组*/
    private $where = [];

    protected function getId()
    {
        return $this->id;
    }

    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    protected function getFrom()
    {
        return $this->from;
    }

    protected function getWhere()
    {
        return $this->where;
    }

    /**
     * 解析字段字符串 如："t1.ib_kcu_id","MAX(t3.sms_lyokhi_flg) AS sms_lyokhi_flg"
     * @param $str
     * @return array
     */
    protected function parseField($str)
    {
        /*todo:*/
        $result = [];
        if (strpos($str, 'AS') !== false) {
            list($str, $result) = $this->parseAs($str, $result);
        }
        if (strpos($str, '(') !== false) {
            $result = $this->parseFunc($str, $result);
        } elseif (strpos($str, '.') !== false) {
            $result = $this->parsePoint($str, $result);
        } else {
            $result = array_push($result, ['']);
        }

        $alias = '';
        if (strpos($str, '(') !== false) {
            $str = [
                'function' => $str,
                'alias' => $alias
            ];
        } elseif (strpos($str, '.') !== false) {
            list($tableAlias, $str) = explode('.', $str);
            $str = [
                'name' => $str,
                'alias' => $alias,
                'tableAlias' => $tableAlias,
                'tableName' => $this->toTableName($tableAlias)
            ];
        } else {
            $table = $this->getFrom()[0];
            $str = [
                'name' => $str,
                'alias' => $alias,
                'tableAlias' => $table['alias'],
                'tableName' => $table['name']
            ];
        }

        return $str;
    }


    /**
     * 解析AS别名 如：SUM(COALESCE(skn_kgk, 0)) AS kingaku
     * @param $str
     * @return array
     */
    protected function parseAs($str, $result)
    {
        $arr = explode('AS', $str);
        $result = array_push($result, $arr[1]);
        return [$arr[0], $result];
    }

    /**
     * 解析表别名和字段 如：t1.ikr_sqh_fil_knr_bng
     * @param $str
     * @return array
     */
    protected function parsePoint($str)
    {
        $arr = explode('.', trim($str));
        return [
            'tableAlias' => trim($arr[0]),
            'name' => trim($arr[1])
        ];
    }

    /**
     * 解析sql方法 如：COUNT(ikt_sqh_bng)
     * @param $str
     * @param $result
     * @return int
     */
    protected function parseFunc($str, $result)
    {
        $arr = [
            'function' => $str
        ];
        $result = array_push($result, $arr);
        return $result;
    }

    /**
     * 解析传入参数字符串 如："#{bankCd,jdbcType=CHAR}"
     * @param $str
     * @return array|bool|string
     */
    protected function parseParameter($str)
    {
        $str = substr($str, strpos($str, '#{') + 2);
        $str = substr($str, 0, strpos($str, ','));
        $str = [
            'parameter' => trim($str)
        ];

        return $str;
    }

}