<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*id例如：execPKTS0005*/
    private $id = null;

    /*sql类型（select、delete、update、insert）*/
    private $sqlType = null;

    /*字段映射数组*/
    private $resultMap = [];

    /*查询字段*/
    private $select = [];

    /*表名*/
    private $from = [];

    /*where条件数组*/
    private $where = [];

    public function __construct()
    {
        $url = url()->current();
        $navActive = '';
        switch ($url) {
            case url('select/index'):
                $navActive = 'select';
                break;
            case url('select/getExcelByTables'):
                $navActive = 'selects';
                break;
            case url('delete/index'):
                $navActive = 'delete';
                break;
            case url('/updateInfo'):
                $navActive = 'log';
                break;
            default:
                break;
        }
        View::share('navActive', $navActive);
    }

    /**
     * 解析 resultMap、sql标签
     * @param $xmls
     * @return $this
     */
    protected function parseXml($xmls)
    {
        if (!$xmls) return $xmls;
        $xmlsArr = $this->explodeXml($xmls);
        if (is_array($xmlsArr)) {
            $this->parseSqlXml($xmlsArr[1]);
            $this->parseResultMapXml($xmlsArr[0]);
        } else {
            $this->parseSqlXml($xmls);
        }
    }

    /**
     * 分拆 resultMap、sql标签
     * @param $xmls
     * @return array
     */
    protected function explodeXml($xmls)
    {
        if (strpos($xmls, '</resultMap>') !== false) {
            $xmls = explode('</resultMap>', $xmls);
            $xmls[0] .= '</resultMap>';
        }

        return $xmls;
    }

    protected function setResultMap($resultMap)
    {
        $this->resultMap = $this->parseResultMapXml($resultMap);
        return $this;
    }

    /**
     * 解析整个resultMap xml标签
     * @param $xml
     * @return $this
     */
    protected function parseResultMapXml($xml)
    {
        if (!$xml) return $xml;
        /*去除换行符*/
        $xml = str_replace(array("\r\n", "\r", "\n"), ' ', $xml);
        /*去除多余空格*/
        $xml = trim(preg_replace("/[\s]+/is", ' ', $xml));

        $select = $this->getSelect();
        do {
            $xml = substr($xml, strpos($xml, 'column="') + 8);
            $arr['column'] = substr($xml, 0, strpos($xml, '"'));
            $xml = substr($xml, strpos($xml, 'property="') + 10);
            $arr['property'] = substr($xml, 0, strpos($xml, '"'));
            $mapArr[] = $arr;

            /*将映射名称写入查询字段数组中*/
            foreach ($select as $key => &$val) {
                if (key_exists('alias', $val) && !empty($val['alias'])) {
                    if ($val['alias'] == $arr['column']) {
                        $val['resultMap'] = $arr['property'];
                    }
                } elseif ($val['name'] == $arr['column']) {
                    $val['resultMap'] = $arr['property'];
                }
            }
        } while (strpos($xml, 'column="') !== false);
        $this->setSelect($select);
        $this->setResultMap($mapArr);

        return $mapArr;
    }

    /**
     * 解析整个sql xml标签
     * @param $xml
     * @return $this
     */
    protected function parseSqlXml($xml)
    {
        if (!$xml) return $xml;
        $xml = $this->strFilter($xml);

        /*解析数据库操作类型（select、delete、update、insert）*/
        $this->setSqlType(substr($xml, 1, 6));

        /*解析xml标签里的id*/
        $this->setId(substr($xml, 12, 12));

        /*解析sql字符串（去除xml标签、去除sql注释、去除多余字符）*/
        $start = strpos($xml, '>') + 2;
        $len = strpos($xml, '</') - $start - 1;
        $xml = substr($xml, $start, $len);
        $strpos = strpos($xml, 'FOR UPDATE');
        if ($strpos !== false) {
            $xml = substr($xml, 0, $strpos);
        } else {
            $strpos = strpos($xml, '--');
            if ($strpos !== false) $xml = substr($xml, 0, $strpos - 1);
        }

        /*解析sql内容*/
        $sqlArr = $this->explodeSelectSql($xml);

        /*先解析表名，然后解析其他*/
        $this->parseFrom($sqlArr['From']);
        unset($sqlArr['From']);

        foreach ($sqlArr as $key => $value) {
            $parseFunc = 'parse' . $key;
            $this->$parseFunc($value);
        }

        return $this->getSql();
    }

    /**
     * 拆分sql字符串
     * @param $sql
     * @return array
     */
    protected function explodeSelectSql($sql)
    {
        $result = [];
        if (!$sql) return $result;
        $posForm = strpos($sql, 'FROM');
        $result['Select'] = substr($sql, 7, $posForm - 8);
        $posWhere = strpos($sql, 'WHERE');
        $result['From'] = substr($sql, $posForm + 5, $posWhere - $posForm - 6);
        $posGroupBy = strpos($sql, 'GROUP BY');
        $posOrderBy = strpos($sql, 'ORDER BY');
        if ($posGroupBy && $posOrderBy) {
            if ($posGroupBy < $posOrderBy) {
                $result['Where'] = substr($sql, $posWhere + 6, $posGroupBy - $posWhere - 7);
                $result['GroupBy'] = substr($sql, $posGroupBy + 9, $posOrderBy - $posGroupBy - 10);
                $result['OrderBy'] = substr($sql, $posOrderBy + 9);
            } else {
                $result['Where'] = substr($sql, $posWhere + 6, $posOrderBy - $posWhere - 7);
                $result['OrderBy'] = substr($sql, $posOrderBy + 9, $posGroupBy - $posOrderBy - 10);
                $result['GroupBy'] = substr($sql, $posGroupBy + 9);
            }
        } elseif ($posGroupBy && !$posOrderBy) {
            $result['Where'] = substr($sql, $posWhere + 6, $posGroupBy - $posWhere - 7);
            $result['GroupBy'] = substr($sql, $posGroupBy + 9);
        } elseif (!$posGroupBy && $posOrderBy) {
            $result['Where'] = substr($sql, $posWhere + 6, $posOrderBy - $posWhere - 7);
            $result['OrderBy'] = substr($sql, $posOrderBy + 9);
        } else {
            $result['Where'] = substr($sql, $posWhere + 6);
        }

        return $result;
    }

    protected function setSelect($select, $filter = false)
    {
        $this->select = $this->parseSelect($select, $filter);
        return $this;
    }

    /**
     * 解析select关键字
     * @param $sql
     * @param bool $filter
     * @return $this
     */
    protected function parseSelect($sql, $filter = false)
    {
        if (!$sql) return $sql;
        if ($filter) $sql = $this->strFilter($sql);

        /*解析字段别名、所属表名*/
        $result = [];
        $selects = explode(',', str_replace(' ', '', $sql));
        foreach ($selects as $key => &$select) {
            $result[] = $this->parseField($select);
        }
        $this->setSelect($result);

        return $result;
    }

    protected function setFrom($from, $filter = false)
    {
        $this->from = $this->parseFrom($from, $filter);
        return $this;
    }

    /**
     * 解析from关键字
     * @param $sql
     * @param bool $filter 是否开启过滤，默认关闭
     * @return $this
     */
    protected function parseFrom($sql, $filter = false)
    {
        if (!$sql) return $sql;
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
        $this->setFrom($result);

        return $result;
    }

    protected function setWhere($where, $filter)
    {
        $this->where = $this->parseWhere($where, $filter);
        return $this;
    }

    /**
     * 解析where关键字
     * @param $sql
     * @param bool $filter
     * @return $this
     */
    protected function parseWhere($sql, $filter = false)
    {
        if (!$sql) return $sql;
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
                } elseif (strpos($where[2], '.') !== false) {
                    $where[2] = $this->parseField($where[2]);
                } else {
                    $where[2] = ['value' => $where[2]];
                }
            }
        }
        $this->setWhere($wheres);

        return $wheres;
    }

    /**
     * 解析字段字符串 如："t1.ib_kcu_id","MAX(t3.sms_lyokhi_flg) AS sms_lyokhi_flg"
     * @param $str
     * @return array
     */
    protected function parseField($str)
    {
        $alias = '';
        if (strpos($str, 'AS') !== false) {
            list($str, $alias) = explode('AS', $str);
        }

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
     * 解析传入参数字符串 如："#{bankCd,jdbcType=CHAR}"
     * @param $str
     * @return array|bool|string
     */
    protected function parseParameter($str)
    {
        $str = substr($str, strpos($str, '#{') + 2);
        $str = substr($str, 0, strpos($str, ','));
        $str = [
            'parameter' => $str
        ];

        return $str;
    }

    /**
     * 解析groupby关键字
     * @param $sql
     * @return $this
     */
    protected function parseGroupBy($sql)
    {
        if (!$sql) return $sql;
    }

    /**
     * 解析orderby关键字
     * @param $sql
     * @return $this
     */
    protected function parseOrderBy($sql)
    {
        if (!$sql) return $sql;
    }

    /**
     * 获取所有标红的字段信息，并以表名分类
     * @return array
     */
    protected function getAllRedFields()
    {
        $result = $this->toClassify($this->getWhereFields());

        return $result;
    }

    /**
     * 获取where条件中的字段
     * @return array
     */
    protected function getWhereFields()
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
    protected function getWhereParameters()
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
     * 获取所有标橙字段的信息，以表名进行分类
     * @return array
     */
    protected function getAllOrangeFields()
    {
        $fields = $this->getSelect();
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) unset($fields[$key]);
        }
        $fields = $this->toClassify($fields);

        return $fields;
    }

    /**
     * 获取所有select条件中的字段
     * @return array
     */
    protected function getSelectFields()
    {
        $fields = $this->getSelect();
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) unset($fields[$key]);
        }

        return $fields;
    }

    /**
     * 以表名对字段进行分类
     * @param $fields
     * @return array
     */
    protected function toClassify($fields)
    {
        $result = [];
        $tables = $this->getFrom();
        foreach ($fields as $key => $field) {
            $result[$field['tableName']][] = $field;
        }
        foreach ($tables as $table) {
            if (!key_exists($table['name'], $result)) $result[$table['name']] = [];
        }

        return $result;
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

    /**
     * 获取解析后的sql信息
     * @return array
     */
    protected function getSql()
    {
        return [
            'id' => $this->getId(),
            'sqlType' => $this->getSqlType(),
            'resultMap' => $this->getResultMap(),
            'select' => $this->getSelect(),
            'from' => $this->getFrom(),
            'where' => $this->getWhere()
        ];
    }

    protected function getId()
    {
        return $this->id;
    }

    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    protected function getResultMap()
    {
        return $this->resultMap;
    }

    protected function getSqlType()
    {
        return $this->sqlType;
    }

    protected function setSqlType($sqlType)
    {
        $this->sqlType = $sqlType;
        return $this;
    }

    protected function getSelect()
    {
        return $this->select;
    }

    protected function getFrom()
    {
        return $this->from;
    }

    protected function getWhere()
    {
        return $this->where;
    }

}

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
