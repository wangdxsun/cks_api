<?php
namespace Admin\Model;

class AccountModel extends  BaseModel{

    public static $table = ['admin', 'role'];
    
    public static function getAccountListData($page = null){

        return BaseModel::joinSecListData(
            [
                'table'=>self::$table[0],
                'joinWhere' => 'role ON admin.role_id = role.id',
                'fields' => ['admin.*','role.role_name'],
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