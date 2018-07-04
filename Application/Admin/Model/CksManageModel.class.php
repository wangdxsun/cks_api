<?php
namespace Admin\Model;



class CksManageModel extends  BaseModel{

    public static $table = ['allot_policy', 'system_keys', 4 => 'channel', 5 => 'pn'];
    
    public static function getPolicyListData($cash, $page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[0],
                'where' => ['cash' => $cash],
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

}