<?php
namespace Admin\Model;
use Think\Model;

/**
 * 角色管理
 * @author jan
 *
 */
class RoleModel extends  BaseModel{

    public static $table = ['role', 'admin'];
    
    public static function getRoleListData($page = null){

        $params = ['table'=>self::$table[0], 'page' => $page = !empty($page) ? $page : 0];

        return BaseModel::getListData($params);

    }

    //删除角色同时清空用户表的角色id
    public static function deleteRole($condition, $condition1){

        $m = M(self::$table[0]);

        $m->startTrans();//开启事务

        $delRoleRes = BaseModel::delData($condition);//先删除角色

        //若admin表存在角色id进行更新
        BaseModel::getDbData($condition1) && ($delAdminRes = BaseModel::setFieldVal($condition1));

        if($delRoleRes === false || $delAdminRes === false){
            $m ->rollback(); //有一个出错回滚
            return false;
        } else{
            $m ->commit();
            return true;
        }

    }

    //菜单和方法的数据集合
    public static function menuAndMethodDataSet(){

        $menus = self:: getAllMenu(false);

        foreach($menus as &$val){

            $val['method'] = BaseModel::getDbData([
                'table' => 'method',
                'where' => ['pid' => $val['id']]
            ]);

            $val['son'] && self::recurveMethod($val['son']);

        }

        return $menus;

    }

    //递归将方法加入到子菜单列表
    private static function recurveMethod(&$data){

        foreach($data as &$val){

            $val['method'] = BaseModel::getDbData([
                'table' => 'method',
                'where' => ['pid' => $val['id']]
            ]);

            $val['son'] && self::recurveMethod($val['son']);
        }
    }






}