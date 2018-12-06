<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use PHPExcel_IOFactory;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*id例如：execPKTS0005*/
    private $id = null;

    /*sql类型（select、delete、update、insert）*/
    private $sqlType = null;

    /*查询字段*/
    private $selects = [];

    /*表名*/
    private $tables = [];

    /*where条件数组*/
    private $wheres = [];

    protected function getSql()
    {
        return [
            'id' => $this->getId(),
            'tables' => $this->getTables(),
            'wheres' => $this->getWheres()
        ];
    }

    /**
     * sql字符串转数组
     * @param $sql
     * @return mixed
     */
    protected function setSql($sql)
    {
        /*去除换行符*/
        $sql = str_replace(array("\r\n", "\r", "\n"), ' ', $sql);
        /*去除多余空格*/
        $sql = trim(preg_replace("/[\s]+/is", ' ', $sql));

        /*解析数据库操作类型（select、delete、update、insert）*/
        $this->setSqlType(substr($sql, 1, 6));

        /*解析xml标签里的id*/
        $this->setId(substr($sql, 12, 12));

        /*解析sql字符串（转小写、去除xml标签、去除sql注释）*/
        $sql = explode('--', strip_tags(strtolower($sql)))[0];

        /*解析sql内容*/
        if ($sql) {
            /*select*/
            list($select, $sql) = explode('from', $sql);
            if ($this->getSqlType() == 'select') {
                $select = substr($select, 8);
                $select = explode(',', str_replace(' ', '', $select));
                $this->setSelect($select);
            }

            /*tables 分割多表、数据库名（"."分隔）、别名(空格分隔)*/
            list($tables, $sql) = explode('where', $sql);
            $tables = explode(',', $tables);
            foreach ($tables as $key => $table) {
                $tablesArr[] = explode(' ', explode('.', trim($table))[1]);
            }
            $this->setTables($tablesArr);

            /*wheres*/
            $wheres = explode('and', $sql);
            foreach ($wheres as $value) {
                $wheresArr[] = explode(' ', trim($value));
            }
            $this->setWheres($wheresArr);
        }

        return $this->getSql();
    }

    protected function getId()
    {
        return $this->id;
    }

    protected function setId($id)
    {
        $this->id = $id;
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

    protected function getTables()
    {
        return $this->tables;
    }

    protected function setTables($tables)
    {
        $this->tables = $tables;
    }

    protected function getWheres()
    {
        return $this->wheres;
    }

    protected function setWheres($wheres)
    {
        $this->wheres = $wheres;
    }

}
