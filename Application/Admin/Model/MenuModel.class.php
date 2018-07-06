<?php
namespace Admin\Model;
use Think\Model;

/**
 * 菜单管理
 *
 * @author jan
 *
 */
class MenuModel extends  BaseModel{

    public static $table = ['menu', 'method'];

    public static function getMenuListData($page = null){

        return self::getMenuPname(BaseModel::getListData(
            [
                'table'=>self::$table[0],
                'order' => 'sort',
                'page' => $page = !empty($page) ? $page : 0
            ]
        ));

    }

    //将pname加入菜单列表
    private static function getMenuPname($menuList){

        foreach($menuList['data'] as &$val){

            $val['pid'] && ($val['pname'] = $val['pname'] = BaseModel::getFieldVal(
                [
                    'table' => self::$table[0],
                    'where' => ['id' => $val['pid']],
                    'field' => 'name',
                ]
            ));
        }

        return $menuList;

    }

    //编辑菜单
    public static function getEditMenuData($menuId){
        //获取菜单
        $menuData = self::getMenuPname(['data' =>BaseModel::getDbData(
            [
                'table' => self::$table[0],
                'where' => ['id' => $menuId ]
            ]
        )]);

        //获取菜单下面的方法
        $methodData = BaseModel::getDbData(
            [
                'table' => self::$table[1],
                'where' => ['pid' => $menuData['data'][0]['id'] ]
            ]
        );

        return ['menu'=>$menuData['data'][0],'method'=>$methodData];

    }

    //删除
    public static function deleteM($condition,$condition1=null)
    {
        if($condition['table'] == self::$table[0]){
            //开启事务
            $m = M(self::$table[0]);
            $m->startTrans();
            //若为父菜单
            if($condition['pid'] == 'no'){//删除的是子菜单

                $delMenuRes = BaseModel::delData($condition);//先删除菜单

                //若存在方法进行删除
                BaseModel::getDbData($condition1) &&($delMethodRes = BaseModel::delData($condition1));

                if($delMenuRes === false || $delMethodRes === false){

                    $m ->rollback(); //有一个出错回滚
                    return false;

                } else{

                    $m ->commit();
                    return true;
                }

            }else{//删除父菜单---先删子菜单--再删父菜单 --还要删除子菜单和父菜单下面的方法
                $delMenuParentRes = BaseModel::delData($condition); //删除父菜单

                BaseModel::getDbData($condition1)
                    && ($delMenuParentMethodRes = BaseModel::delData($condition1));//删除父菜单拥有的方法

                //子菜单条件
                $condition2 = [

                    'table' => $condition['table'],
                    'where' => ['pid' =>$condition['pid'] ]

                ];

                ($childMenu = BaseModel::getDbData($condition2))
                    && ($delMenuChildRes = BaseModel::delData($condition2));//若有子菜单删除子菜单

                //子菜单方法条件
                $condition2 = [

                    'table' => $condition1['table'],
                    'where' => ['pid' =>['in', array_column($childMenu, 'id')]]

                ];

                $childMenu
                     && ($childMethod = BaseModel::getDbData($condition2)
                        && ($delMenuChildMethodRes = BaseModel::delData($condition2)));//若子菜单存在再去查是否子菜单下面有方法

                /*$childMethod && ($delMenuChildMethodRes = BaseModel::delData([

                    'table' => $condition1['table'],
                    'where' => ['pid' =>['in', array_column($childMenu, 'id')]]

                ]));*///若子菜单拥有方法删除子菜单拥有的方法

                if( $delMenuChildRes === false
                    || $delMenuChildMethodRes === false
                    || $delMenuParentRes === false
                    || $delMenuParentMethodRes === false){

                    $m ->rollback(); //有一个出错回滚
                    return false;

                } else{

                    $m ->commit();
                    return true;
                }
            }



        }else

            return BaseModel::delData($condition);

    }

}