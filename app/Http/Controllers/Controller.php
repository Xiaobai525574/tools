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

        /*解析sql字符串（转小写、去除xml标签、去除sql注释、去除多余字符）*/
        $sql = strip_tags(strtolower($sql));
        $strpos = strpos($sql, 'for update');
        if ($strpos !== false) {
            $sql = substr($sql, 0, $strpos);
        } else {
            $strpos = strpos($sql, '--');
            if ($strpos !== false) $sql = substr($sql, 0, $strpos);
        }
        $sql = trim($sql);
        if (!$sql) return $this->getSql();

        /*解析sql内容*/
        /*selects*/
        list($selects, $sql) = explode('from', $sql);
        $selects = substr($selects, 7);
        if ($this->getSqlType() == 'select') {
            $this->parseSelects($selects);
        }
        if (!$sql) return $this->getSql();

        /*tables 分割多表、数据库名（"."分隔）、别名(空格分隔)*/
        list($tables, $sql) = explode('where', $sql);
        $this->parseTables($tables);
        if (!$sql) return $this->getSql();

        /*wheres*/
        $this->parseWheres($sql);

        return $this->getSql();
    }

    protected function parseSelects($sql)
    {
        if (!$sql) return $sql;

        /*去除换行符*/
        $sql = str_replace(array("\r\n", "\r", "\n"), ' ', strtolower($sql));
        /*去除多余空格*/
        $sql = trim(preg_replace("/[\s]+/is", ' ', $sql));
        $selects = explode(',', str_replace(' ', '', $sql));
        /*解析别名*/
        foreach ($selects as $key => &$select) {
            if (strpos($select, '.') !== false) {
                $select = explode('.', $select);
            } else {
                $select = ['', $select];
            }
        }
        $this->setSelects($selects);

        return $selects;
    }

    protected function parseTables($sql)
    {
        if (!$sql) return $sql;

        /*去除换行符*/
        $sql = str_replace(array("\r\n", "\r", "\n"), ' ', strtolower($sql));
        /*去除多余空格*/
        $sql = trim(preg_replace("/[\s]+/is", ' ', $sql));
        $tables = explode(',', $sql);
        foreach ($tables as $key => &$table) {
            $table = explode(' ', explode('.', trim($table))[1]);
        }
        $this->setTables($tables);

        return $tables;
    }

    protected function parseWheres($sql)
    {
        if (!$sql) return $sql;

        /*去除换行符*/
        $sql = str_replace(array("\r\n", "\r", "\n"), ' ', strtolower($sql));
        /*去除多余空格*/
        $sql = trim(preg_replace("/[\s]+/is", ' ', $sql));
        $wheres = explode('and', $sql);
        /*解析别名*/
        foreach ($wheres as &$where) {
            $where = explode(' ', trim($where));
            if (strpos($where[0], '.') !== false) {
                $where[0] = explode('.', $where[0]);
            } else {
                $where[0] = ['', $where[0]];
            }
        }
        $this->setWheres($wheres);
        return $wheres;
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

    protected function getSelects()
    {
        return $this->selects;
    }

    protected function setSelects($selects)
    {
        $this->selects = $selects;
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
