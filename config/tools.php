<?php
return [
    'storage' => [
        /*excel模板存放路径*/
        'tablesPath' => '\\public\\tables\\',
        /**/
        'deletePath' => '\\public\\delete\\',
        /*select Excel生成以后的存放路径*/
        'selectPath' => '\\public\\select\\',
        /*数据库式样书的存放路径*/
        'tablesDefinitionPath' => 'app\\public\\tablesDefinition\\',
        /*需要检测位数是否符合要求的excel路径*/
        'checkExcels' => 'app\\public\\checkExcels\\'
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
        'red' => 'FFFF0000',
        'orange' => 'FFFFC000',
        'yellow' => 'FFFFFF00',
        'green' => 'FF92D050'
    ]
];
