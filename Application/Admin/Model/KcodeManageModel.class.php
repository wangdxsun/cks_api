<?php
namespace Admin\Model;

class KcodeManageModel extends  BaseModel{

    public static $table = ['kd_relation', 'role'];
    
    public static function getAllocatListData($page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[0],
                'fields' => ['pnumber', 'pname', 'money'],
                'where' => ['status' => 1],
                'order' => 'allot_time',
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

    //角色列表
    public static function getRoleName(){

        return BaseModel::getDbData(
            [
                'table'=>self::$table[1],
                'fields'=>['id', 'role_name'],
            ]);
    }



}