<?php
namespace Admin\Model;

class SystemKeysModel extends  BaseModel{

    public static $table = ['system_keys'];
    
    public static function getSystemKeysListData($page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[0],
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

    //获取系统配置的键值
    public static function getSystemKeys($field, $order=false){

        return BaseModel::getDbData([

            'table' => SystemKeysModel::$table[0],
            'fields' => ['key2', 'value1', 'value2', 'value3'],
            'order' => $order,
            'where' => ['key1' => $field ]

        ]);
    }


    //通过key2 获取value1

    public static function getSystemKeyValue($field, $key2, $fieldKey){
        //echo $key2;die;

        return BaseModel::getDbData([

            'table' => SystemKeysModel::$table[0],
            'fields' => ['key2', 'value1'],
            'where' => ['key1' => $field,  $fieldKey => $key2]

        ], false);

    }

    //通过value1 获取key2

}