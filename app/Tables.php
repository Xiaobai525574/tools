<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{

    /**
     * 根据表名查询主键、唯一键
     * @param $tableName
     * @return mixed
     */
    public function getKeys($tableName)
    {
        $keys = $this->select('primary_key', 'unique_key')
            ->where('table_name', '=', $tableName)
            ->first()
            ->toArray();
        $keys['primary_key'] = explode(',', $keys['primary_key']);
        $keys['unique_key'] = explode(',', $keys['unique_key']);

        return $keys;
    }
}
