<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/12/28
 * Time: 17:20
 */

namespace App\Http\Services\SqlService;


class SelectSql
{
    /*sql类型（select、delete、update、insert）*/
    private $sqlType = null;

    /*字段映射数组*/
    private $resultMap = [];

    /*查询字段*/
    private $select = [];

    /*orderBy条件数组*/
    private $orderBy = [];

    /*groupBy条件数组*/
    private $groupBy = [];

    protected function getResultMap()
    {
        return $this->resultMap;
    }

    protected function getSelect()
    {
        return $this->select;
    }

    protected function getOrderBy()
    {
        return $this->orderBy;
    }

    protected function getGroupBy()
    {
        return $this->groupBy;
    }
}