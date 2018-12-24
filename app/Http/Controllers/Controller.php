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
            $this->parseSqlXml($xmlsArr[1]);
            $this->parseResultMapXml($xmlsArr[0]);
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

        $select = $this->getSelect();
        do {
            $xml = substr($xml, strpos($xml, 'column="') + 8);
            $arr['column'] = substr($xml, 0, strpos($xml, '"'));
            $xml = substr($xml, strpos($xml, 'property="') + 10);
            $arr['property'] = substr($xml, 0, strpos($xml, '"'));
            $mapArr[] = $arr;

            /*将映射名称写入查询字段数组中*/
            foreach ($select as $key => &$val) {
                if ($val['name'] == $arr['column']) {
                    $val['resultMap'] = $arr['property'];
                }
            }
        } while (strpos($xml, 'column="') !== false);
        $this->setSelect($select);
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
            $result[] = $this->parseField($select);
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
                    $where[2] = ['value' => $where[2]];
                }
            }
        }
        $this->setWhere($wheres);
        return $wheres;
    }

    protected function parseField($str)
    {
        $alias = '';
        $tableAlias = '';
        if (strpos($str, '.') !== false) {
            list($tableAlias, $str) = explode(',', $str);
        }
        if (strpos($str, 'AS') !== false) {
            list($str, $alias) = explode('AS', $str);
        }
        $str = [
            'name' => $str,
            'alias' => $alias,
            'tableAlias' => $tableAlias,
            'tableName' => $this->toTableName($tableAlias)
        ];

        return $str;
    }

    protected function parseParameter($str)
    {
        $str = substr($str, strpos($str, '#{') + 2);
        $str = substr($str, 0, strpos($str, ','));
        $str = [
            'parameter' => $str
        ];

        return $str;
    }

    protected function parseGroupBy($sql)
    {

    }

    protected function parseOrderBy($sql)
    {

    }

    protected function getWhereFields()
    {
        $result = [];
        $where = $this->getWhere();
        foreach ($where as $key => $value) {
            if (key_exists($value[0]['name'])) {
                $result[] =  $value[0];
            }
            if (key_exists($value[2]['name'])) {
                $result[] =  $value[2];
            }
        }

        return $result;
    }

    protected function getWhereParameters()
    {
        $result = [];
        $where = $this->getWhere();
        foreach ($where as $key => $value) {
            if (key_exists('parameter', $value[0])) {
                $value[2]['parameter'] = $value[0]['parameter'];
                $result[] = $value[2];
            } elseif (key_exists('parameter', $value[2])) {
                $value[0]['parameter'] = $value[2]['parameter'];
                $result[] = $value[0];
            }
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
