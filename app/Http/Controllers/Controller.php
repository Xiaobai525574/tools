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

    protected function getSql()
    {
        return [
            'id' => $this->getId(),
            'tables' => $this->getFrom(),
            'wheres' => $this->getWhere()
        ];
    }

    protected function parseXml($xmls)
    {
        $xmlsArr = $this->explodeXml($xmls);
        if ($xmlsArr) {
            $this->parseResultMapXml($xmlsArr[0]);
            $this->parseSqlXml($xmlsArr[1]);
        } else {
            $this->parseSqlXml($xmls);
        }
    }

    protected function explodeXml($xmls)
    {
        $xmlArr = [];
        if (strpos($xmls, '</resultMap>') !== false) {
            $xmlArr = explode('</resultMap>', $xmls);
            $xmlArr[0] .= '</resultMap>';
        }

        return $xmlArr;
    }

    protected function parseResultMapXml($xml)
    {
        /*去除换行符*/
        $xml = str_replace(array("\r\n", "\r", "\n"), ' ', $xml);
        /*去除多余空格*/
        $xml = trim(preg_replace("/[\s]+/is", ' ', $xml));

        do {
            $xml = substr($xml, strpos($xml, 'column="') + 8);
            $arr['column'] = substr($xml, 0, strpos($xml, '"'));
            $xml = substr($xml, strpos($xml, 'property="') + 10);
            $arr['property'] = substr($xml, 0, strpos($xml, '"'));
            $mapArr[] = $arr;
        } while (strpos($xml, 'column="') !== false);

        $this->setResultMap($mapArr);

        return $mapArr;
    }

    protected function parseSqlXml($xml)
    {
        $xml = $this->strFilter($xml);

        /*解析数据库操作类型（select、delete、update、insert）*/
        $this->setSqlType(substr($xml, 1, 6));

        /*解析xml标签里的id*/
        $this->setId(substr($xml, 12, 12));

        /*解析sql字符串（去除xml标签、去除sql注释、去除多余字符）*/
        $xml = strip_tags($xml);
        $strpos = strpos($xml, 'FOR UPDATE');
        if ($strpos !== false) {
            $xml = substr($xml, 0, $strpos);
        } else {
            $strpos = strpos($xml, '--');
            if ($strpos !== false) $xml = substr($xml, 0, $strpos);
        }
        $xml = trim($xml);
        if (!$xml) return $this->getSql();

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

    protected function parseSelect($sql, $filter = false)
    {
        if (!$sql) return $sql;
        if ($filter) $sql = $this->strFilter($sql);

        /*解析字段别名、所属表名*/
        $result = [];
        $selects = explode(',', str_replace(' ', '', $sql));
        foreach ($selects as $key => &$select) {
            if (strpos($select, '.') !== false) {
                list($tableAlias, $select) = explode('.', $select);
            } else {
                $tableAlias = '';
            }
            $select = explode('AS', $select);
            if (count($select) == 1) $select[1] = '';
            $result[$this->toTableName($tableAlias)][] = [
                'name' => $select[0],
                'alias' => $select[1]
            ];
        }
        $this->setSelect($result);

        return $result;
    }

    /**
     * 解析from关键字
     * @param $sql
     * @param bool $filter 是否开启过滤，默认关闭
     * @return array|mixed|string
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

    protected function parseWhere($sql, $filter = false)
    {
        if (!$sql) return $sql;
        if ($filter) $sql = $this->strFilter($sql);

        $wheres = explode('AND', $sql);
        /*解析别名*/
        foreach ($wheres as &$where) {
            /*todo:拆分出字段名、参数、常数*/
            $where = explode(' ', trim($where));
            if (strpos($where[0], '.') !== false) {
                $where[0] = explode('.', $where[0]);
                $where[0][0] = $this->toTableName($where[0][0]);
            } elseif (strpos($where[0], '#{') !== false) {
                $where[0] = substr($where[0], strpos($where[0], '#{') + 2);
                $param = substr($param, 0, strpos($param, ','));
                $str = ['isParam', $param];
            } else {
                $str = ['', $str];
            }
        }
        $this->setWhere($wheres);
        return $wheres;
    }

    protected function parseGroupBy($sql)
    {

    }

    protected function parseOrderBy($sql)
    {

    }

    /**
     * 解析where条件运算符左右两边的参数
     * @param $str
     * @return array
     */
    protected function parseParameters($str)
    {
        if (strpos($str, '.') !== false) {
            $str = explode('.', $str);
            $str[0] = $this->toTableName($str[0]);
        } elseif (strpos($str, '#{') !== false) {
            $param = substr($str, strpos($str, '#{') + 2);
            $param = substr($param, 0, strpos($param, ','));
            $str = ['isParam', $param];
        } else {
            $str = ['', $str];
        }

        return $str;
    }

    /**
     * 获取where条件中出现的字段
     */
    protected function getWhereFields()
    {
        $wheres = $this->getWhere();
        $wheresLeft = array_column($wheres, 0);
        $wheresRight = array_column($wheres, 2);
        $result = [];

        foreach ($wheresLeft as $key => $value) {
            if ($value[0] == 'isParam') {
                $value = $wheresRight[$key];
            }
            if ($value[0] == '') {
                $result[0][] = $value[1];
            } else {
                $result[$value[0]][] = $value[1];
            }
        }

        return $result;
    }

    protected function getCodeInputs()
    {
        $wheres = $this->getWhere();
        $wheresLeft = array_column($wheres, 0);
        $wheresRight = array_column($wheres, 2);
        $tables = $this->getFrom();
        $result = [];
        $param = [];
        $tableName = '';

        foreach ($wheresLeft as $key => $value) {
            if ($value[0] == 'isParam') {
                $param['column'] = $wheresRight[$key][1];
                $param['property'] = $value[1];
                if ($wheresRight[$key][0] == '') {
                    $tableName = $tables[0][0];
                } else {
                    foreach ($tables as $k => $v) {
                        if ($v[1] == $wheresRight[$key][0]) {
                            $tableName = $v[0];
                        }
                    }
                }
            } elseif ($wheresRight[$key][0] == 'isParam') {
                $param['column'] = $value[1];
                $param['property'] = $wheresRight[$key][1];
                if ($value[0] == '') {
                    $tableName = $tables[0][0];
                } else {
                    foreach ($tables as $k => $v) {
                        if ($v[1] == $value[0]) {
                            $tableName = $v[0];
                        }
                    }
                }
            }
            $result[$tableName][] = $param;
        }

        return $result;
    }

    protected function getCodeOutputs()
    {
        $selects = $this->getSelect();
        $resultMap = $this->getResultMap();
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

    protected function strFilter($str)
    {
        /*去除换行符*/
        $str = str_replace(array("\r\n", "\r", "\n"), ' ', $str);
        /*去除多余空格*/
        $str = trim(preg_replace("/[\s]+/is", ' ', $str));

        return $str;
    }

    protected function getId()
    {
        return $this->id;
    }

    protected function setId($id)
    {
        $this->id = $id;
    }

    protected function getResultMap()
    {
        return $this->resultMap;
    }

    protected function setResultMap($resultMap)
    {
        $this->resultMap = $resultMap;
    }

    protected function getSqlType()
    {
        return $this->sqlType;
    }

    protected function setSqlType($sqlType)
    {
        $this->sqlType = $sqlType;
    }

    protected function getSelect()
    {
        return $this->select;
    }

    protected function setSelect($select)
    {
        $this->select = $select;
    }

    protected function getFrom()
    {
        return $this->from;
    }

    protected function setFrom($from)
    {
        $this->from = $from;
    }

    protected function getWhere()
    {
        return $this->where;
    }

    protected function setWhere($where)
    {
        $this->where = $where;
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
