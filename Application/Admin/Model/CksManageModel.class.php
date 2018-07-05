<?php
namespace Admin\Model;



class CksManageModel extends  BaseModel{

    public static $table = ['allot_policy', 'system_keys', 4 => 'channel', 5 => 'pn_type'];
    public static $field = [ 4=>'channel_name', 5 => 'pnumber'];

    public static function getPolicyListData($cash, $page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[0],
                'where' => ['cash' => $cash],
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

    public static function getDimSearchData($tag, $describe){

        return [ 'data' => BaseModel::getDbData([

            'table' => self::$table[$tag],
            'fields' => [self::$field[$tag]],
            'where' => [ self::$field[$tag] => ['like', "%".$describe."%"]  ],

        ]), 'field' =>self::$field[$tag]];

    }

}