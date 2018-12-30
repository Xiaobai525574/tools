<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:20
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

/**
 * Class SqlSelect
 * @package App\Http\Services\SqlService
 */
class SqlSelect extends Sql
{

    /*字段映射数组*/
    private $resultMap = [];

    /*查询字段*/
    private $select = [];

    /*orderBy条件数组*/
    private $orderBy = [];

    /*groupBy条件数组*/
    private $groupBy = [];

    public function getResultMap()
    {
        return $this->resultMap;
    }

    public function setResultMap($resultMap)
    {
        $this->resultMap = $resultMap;
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function setSelect($select)
    {
        $this->select = $select;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function getGroupBy()
    {
        return $this->groupBy;
    }

    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    /**
     * 获取解析后的sql信息
     * @return array
     */
    public function getSql()
    {
        return [
            'id' => $this->getId(),
            'resultMap' => $this->getResultMap(),
            'select' => $this->getSelect(),
            'from' => $this->getFrom(),
            'where' => $this->getWhere(),
            'orderBy' => $this->getOrderBy(),
            'groupBy' => $this->getGroupBy()
        ];
    }


    /**
     * 解析 resultMap、sql标签
     * @param $xml
     * @return bool
     */
    public function parseXml($xml)
    {
        if (!$xml) return false;
        $xmlArr = $this->explodeXml($xml);
        if (is_array($xmlArr)) {
            $this->parseSqlXml($xmlArr[1]);
            $this->resultMap($xmlArr[0]);
        } else {
            $this->parseSqlXml($xml);
        }
        return true;
    }

    /**
     * 拆分 resultMap、sql标签
     * @param $xml
     * @return array
     */
    public function explodeXml($xml)
    {
        if (strpos($xml, '</resultMap>') !== false) {
            $xml = explode('</resultMap>', $xml);
            $xml[0] .= '</resultMap>';
        }

        return $xml;
    }

    public function resultMap($resultMap)
    {
        if (!$resultMap) return $this;
        $resultMap = $this->parseResultMapXml($resultMap);
        /*将映射名称写入查询字段数组中*/
        $select = $this->getSelect();
        foreach ($resultMap as $k => $v) {
            foreach ($select as $key => &$val) {
                if (key_exists('alias', $val) && !empty($val['alias'])) {
                    if ($val['alias'] == $v['column']) {
                        $val['resultMap'] = $v['property'];
                    }
                } elseif ($val['name'] == $v['column']) {
                    $val['resultMap'] = $v['property'];
                }
            }
        }
        $this->setResultMap($resultMap);
        $this->setSelect($select);

        return $this;
    }

    /**
     * 解析整个resultMap xml标签
     * @param $xml
     * @return array|bool|mixed|string
     */
    public function parseResultMapXml($xml)
    {
        if (!$xml) return false;
        $xml = $this->strFilter($xml);

        do {
            $xml = substr($xml, strpos($xml, 'column="') + 8);
            $arr['column'] = substr($xml, 0, strpos($xml, '"'));
            $xml = substr($xml, strpos($xml, 'property="') + 10);
            $arr['property'] = substr($xml, 0, strpos($xml, '"'));
            $mapArr[] = $arr;
        } while (strpos($xml, 'column="') !== false);

        return $mapArr;
    }

    /**
     * 解析整个sql xml标签
     * @param $xml
     * @return array|bool|mixed|string
     */
    public function parseSqlXml($xml)
    {
        if (!$xml) return false;
        $xml = $this->strFilter($xml);

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
        $sqlArr = $this->explodeSql($xml);

        /*先解析表名，然后解析其他*/
        foreach ($sqlArr as $key => $value) {
            $this->$key($value);
        }

        return $this->getSql();
    }

    /**
     * 拆分sql字符串
     * @param $sql
     * @return array|bool
     */
    public function explodeSql($sql)
    {
        $result = [
            'from' => [],
            'select' => [],
            'where' => [],
            'groupBy' => [],
            'orderBy' => []
        ];
        if (!$sql) return false;
        $posForm = strpos($sql, 'FROM');
        $result['select'] = substr($sql, 7, $posForm - 8);
        $posWhere = strpos($sql, 'WHERE');
        $result['from'] = substr($sql, $posForm + 5, $posWhere - $posForm - 6);
        $posGroupBy = strpos($sql, 'GROUP BY');
        $posOrderBy = strpos($sql, 'ORDER BY');
        if ($posGroupBy && $posOrderBy) {
            if ($posGroupBy < $posOrderBy) {
                $result['where'] = substr($sql, $posWhere + 6, $posGroupBy - $posWhere - 7);
                $result['groupBy'] = substr($sql, $posGroupBy + 9, $posOrderBy - $posGroupBy - 10);
                $result['orderBy'] = substr($sql, $posOrderBy + 9);
            } else {
                $result['where'] = substr($sql, $posWhere + 6, $posOrderBy - $posWhere - 7);
                $result['orderBy'] = substr($sql, $posOrderBy + 9, $posGroupBy - $posOrderBy - 10);
                $result['groupBy'] = substr($sql, $posGroupBy + 9);
            }
        } elseif ($posGroupBy && !$posOrderBy) {
            $result['where'] = substr($sql, $posWhere + 6, $posGroupBy - $posWhere - 7);
            $result['groupBy'] = substr($sql, $posGroupBy + 9);
        } elseif (!$posGroupBy && $posOrderBy) {
            $result['where'] = substr($sql, $posWhere + 6, $posOrderBy - $posWhere - 7);
            $result['orderBy'] = substr($sql, $posOrderBy + 9);
        } else {
            $result['where'] = substr($sql, $posWhere + 6);
        }

        return $result;
    }

    public function select($select, $filter = false)
    {
        if (!$select) return $this;
        $this->select = $this->parseSelect($select, $filter);
        return $this;
    }

    /**
     * 解析select关键字
     * @param $sql
     * @param bool $filter 是否开启过滤，默认关闭
     * @return array|mixed|string
     */
    public function parseSelect($sql, $filter = false)
    {
        if (!$sql) return false;
        if ($filter) $sql = $this->strFilter($sql);

        /*解析字段别名、所属表名*/
        $result = [];
        $selects = explode(',', str_replace(' ', '', $sql));
        foreach ($selects as $key => &$select) {
            $result[] = $this->parseField($select);
        }

        return $result;
    }

    public function groupBy($groupBy, $filter = false)
    {
        if (!$groupBy) return $this;
        $this->groupBy = $this->parseGroupBy($groupBy, $filter);
        return $this;
    }

    /**
     * 解析groupby关键字
     * @param $sql
     * @param bool $filter
     * @return bool
     */
    public function parseGroupBy($sql, $filter = false)
    {
        if (!$sql) return false;
        if ($filter) $sql = $this->strFilter($sql);

        return $sql;
    }

    public function orderBy($orderBy, $filter = false)
    {
        if (!$orderBy) return $this;
        $this->orderBy = $this->parseOrderBy($orderBy, $filter);
        return $this;
    }

    /**
     * 解析orderby关键字
     * @param $sql
     * @param bool $filter
     * @return bool
     */
    public function parseOrderBy($sql, $filter = false)
    {
        if (!$sql) return false;
        if ($filter) $sql = $this->strFilter($sql);

        return $sql;
    }

    /**
     * 获取所有select条件中的字段，以表名进行分组
     * @return array
     */
    public function getSelectFieldsGroup()
    {
        $fields = $this->getSelect();
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) unset($fields[$key]);
        }
        $fields = $this->groupFields($fields);

        return $fields;
    }

    /**
     * 获取所有select条件中的字段
     * @return array
     */
    public function getSelectFields()
    {
        $fields = $this->getSelect();
        foreach ($fields as $key => $field) {
            if (!key_exists('name', $field)) unset($fields[$key]);
        }

        return $fields;
    }

}
