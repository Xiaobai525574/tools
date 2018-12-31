<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:18
 */

namespace App\Http\Services\SqlService;

/**
 *                       .::::.
 *                     .::::::::.
 *                    :::::::::::
 *                 ..:::::::::::'
 *              '::::::::::::'
 *                .::::::::::
 *           '::::::::::::::..
 *                ..::::::::::::.
 *              ``::::::::::::::::
 *               ::::``:::::::::'        .:::.
 *              ::::'   ':::::'       .::::::::.
 *            .::::'      ::::     .:::::::'::::.
 *           .:::'       :::::  .:::::::::' ':::::.
 *          .::'        :::::.:::::::::'      ':::::.
 *         .::'         ::::::::::::::'         ``::::.
 *     ...:::           ::::::::::::'              ``::.
 *    ```` ':.          ':::::::::'                  ::::..
 *                       '.:::::'                    ':'````..
 *
 */

class Sql
{
    /*id例如：execPKTS0005*/
    protected $id = null;

    /*表名*/
    protected $from = [];

    /*where条件数组*/
    protected $where = [];

    /*当前操作field*/
    protected $field = [
        'name' => '',
        'alias' => '',
        'tableName' => '',
        'tableAlias' => ''
    ];

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
    }

    protected function getField()
    {
        return $this->field;
    }

    protected function setField($field)
    {
        $this->field = $field;
    }

    /**
     * 重置并返回当前操作字段
     * @return array
     */
    protected function resetField()
    {
        $field = [
            'name' => '',
            'alias' => '',
            'tableName' => '',
            'tableAlias' => ''
        ];
        $this->field = $field;

        return $field;
    }

    public function from($from, $filter = false)
    {
        if (!$from) return $this;
        $this->from = $this->parseFrom($from, $filter);
        return $this;
    }

    /**
     * 解析from关键字
     * @param $sql
     * @param bool $filter 是否开启过滤，默认关闭
     * @return array|mixed|string
     */
    public function parseFrom($sql, $filter = false)
    {
        if (!$sql) return false;
        if ($filter) $sql = $this->strFilter($sql);

        $result = [];
        $tables = explode(',', $sql);
        foreach ($tables as $key => &$table) {
            $table = explode(' ', explode('.', trim($table))[1]);
            if (count($table) == 1) $table[1] = '';
            $result[] = [
                'name' => $table[0],
                'alias' => $table[1]
            ];
        }

        return $result;
    }

    public function where($where, $filter = false)
    {
        if (!$where) return $this;
        $this->where = $this->parseWhere($where, $filter);
        return $this;
    }

    /**
     * 解析where关键字
     * @param $sql
     * @param bool $filter 是否开启过滤，默认关闭
     * @return $this
     */
    public function parseWhere($sql, $filter = false)
    {
        if (!$sql) return false;
        if ($filter) $sql = $this->strFilter($sql);

        /*解析别名*/
        $wheres = explode('AND', $sql);
        foreach ($wheres as &$where) {
            $where = explode(' ', trim($where));
            if (strpos($where[0], '#{') !== false) {
                $where[0] = $this->parseParameter($where[0]);
                $where[2] = $this->parseField($where[2]);
            } else {
                $where[0] = $this->parseField($where[0]);
                if (strpos($where[2], '#{') !== false) {
                    $where[2] = $this->parseParameter($where[2]);
                } else {
                    $where[2] = $this->parseField($where[2]);
                }
            }
        }

        return $wheres;
    }

    /**
     * 获取where条件中的字段，并以表名分组
     * @return array
     */
    public function getWhereFieldsGroup()
    {
        $result = $this->groupFields($this->getWhereFields());

        return $result;
    }

    /**
     * 获取where条件中的字段
     * @return array
     */
    public function getWhereFields()
    {
        $result = [];
        $where = $this->getWhere();
        foreach ($where as $key => $value) {
            for ($i = 0; $i < 3; $i = $i + 2) {
                if (key_exists('name', $value[$i])) {
                    $result[] = $value[$i];
                }
            }
        }

        return $result;
    }

    /**
     * 获取where条件中传入的参数集合并去重
     * @return array
     */
    public function getWhereParameters()
    {
        $result = [];
        $where = $this->getWhere();
        $uniqueArr = [];
        foreach ($where as $key => $value) {
            if (key_exists('parameter', $value[0]) || key_exists('parameter', $value[2])) {
                $value = array_merge($value[0], $value[2]);
                //去重
                if (!in_array($value['parameter'], $uniqueArr)) {
                    $uniqueArr[] = $value['parameter'];
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 以表名对字段进行分类
     * @param $fields
     * @return array
     */
    public function groupFields($fields)
    {
        $result = [];
        $tables = $this->getFrom();
        foreach ($fields as $key => $field) {
            if (key_exists('function', $field)) {
                $result['function'][] = $field;
            } else {
                $result[$field['tableName']][] = $field;
            }
        }
        foreach ($tables as $table) {
            if (!key_exists($table['name'], $result)) $result[$table['name']] = [];
        }

        return $result;
    }

    /**
     * 解析字段字符串 如："t1.ib_kcu_id","MAX(t3.sms_lyokhi_flg) AS sms_lyokhi_flg"
     * @param $str
     * @return array
     */
    protected function parseField($str)
    {
        $result = $this->resetField();
        if (strpos($str, 'AS') !== false) {
            list($str, $result) = $this->parseAs($str);
        }
        if (strpos($str, '(') !== false) {
            $result = $this->parseFunc($str);
        } elseif (strpos($str, '.') !== false) {
            $result = $this->parsePoint($str);
        } else {
            $result['name'] = trim($str);
        }
        $this->resetField();

        return $result;
    }

    /**
     * 解析AS别名 如：SUM(COALESCE(skn_kgk, 0)) AS kingaku
     * @param $str
     * @return array
     */
    protected function parseAs($str, $reset = false)
    {
        $result = $reset ? $this->resetField() : $this->getField();
        $arr = explode('AS', $str);
        $result['alias'] = trim($arr[1]);
        if (!$reset) $this->setField($result);

        return [$arr[0], $result];
    }

    /**
     * 解析表别名和字段 如：t1.ikr_sqh_fil_knr_bng
     * @param $str
     * @return array
     */
    protected function parsePoint($str, $reset = false)
    {
        $result = $reset ? $this->resetField() : $this->getField();
        $arr = explode('.', $str);
        $result['tableAlias'] = trim($arr[0]);
        $result['tableName'] = $this->toTableName($result['tableAlias']);
        $result['name'] = trim($arr[1]);
        if (!$reset) $this->setField($result);

        return $result;
    }

    /**
     * 解析sql方法 如：COUNT(ikt_sqh_bng)
     * @param $str
     * @return array
     */
    protected function parseFunc($str, $reset = false)
    {
        $result = $reset ? $this->resetField() : $this->getField();
        $result['function'] = trim($str);
        unset($result['name']);
        if (!$reset) $this->setField($result);

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

    /**
     * 根据别名获取表名
     * @param $alias
     * @return bool
     */
    protected function toTableName($alias)
    {
        $tables = $this->getFrom();
        foreach ($tables as $key => $table) {
            if ($alias == $table['alias']) {
                return $table['name'];
            }
        }

        return false;
    }

    /**
     * 字符创过滤，去除换行符、多余空格等
     * @param $str
     * @return mixed|string
     */
    protected function strFilter($str)
    {
        /*去除换行符*/
        $str = str_replace(array("\r\n", "\r", "\n"), ' ', $str);
        /*去除多余空格*/
        $str = trim(preg_replace("/[\s]+/is", ' ', $str));

        return $str;
    }

}