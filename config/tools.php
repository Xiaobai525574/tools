<?php
return [
    'storage' => [
        'tablesPath' => '\\public\\tables\\',
        'deletePath' => '\\public\\delete\\',
        'selectPath' => '\\public\\select\\',
        'tablesDefinitionPath' => 'app\\public\\tablesDefinition\\'
    ],
    'excel' => [
        /*Excel格式*/
        'type' => 'xlsx',
        /*sql数据在Excel中的起始行*/
        'startRow' => 2,
        /*表重命名处理页的title*/
        'renameTableTitle' => '__RENAME_TABLE'
    ],
    'color' => [
        'red' => 'FF0000',
        'orange' => 'ffc000',
        'yellow' => '',
    ]
];
