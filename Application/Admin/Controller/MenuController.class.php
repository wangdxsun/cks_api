<?php

namespace Admin\Controller;
use Admin\Model\BaseModel;
use Admin\Model\MenuModel;

/**
 * 菜单管理
 * 
 * @author jan
 *
 */
class MenuController extends BaseController
{

    //菜单列表
    public function index()
    {

       $this->assign('data', MenuModel::getMenuListData($_GET['page']));//分页 和菜单列表
       $this->assign('menuRate', MenuModel::getAllMenu());//分级菜单
       $this->display();
    }

    //编辑菜单
    public function getEditMenuData(){

        $this->ajaxReturn(MenuModel::getEditMenuData($_POST['id']));
    }

    //获取一条方法数据
    public function getOneMethodData(){

        $this->ajaxReturn(BaseModel::getDbData([

            'table' => MenuModel::$table[1],
            'where' => ['id' => $_POST['id'] ]

        ],false));

    }

    //删除
    public function deleteM(){

        if ($_POST['table'] == MenuModel::$table[0])

            $this->delRemind(MenuModel::deleteM(
                [
                    'table' => MenuModel::$table[0],
                    'where' => ['id' => $_POST['id']],
                    'pid' => $_POST['pid']
                ],
                [
                    'table' => MenuModel::$table[1],
                    'where' => ['pid' => $_POST['id']],
                ]));
        else

            $this->delRemind(MenuModel::deleteM([

                'table' => MenuModel::$table[1],
                'where' => ['id' => $_POST['id']]

            ]));

    }

    //数据封装
    public function checkPost($postData){
        $data = [];
        //处理菜单
        if($postData['table'] == MenuModel::$table[0]){
            $data['table'] = MenuModel::$table[0];
            $data['data']['name'] = trim($postData['menuname']);

            if($postData['menuid']){
                $data['id'] = $postData['menuid'];
                $data['tag'] = 'edit';
            }else{
                $data['tag'] = 'add';
                $data['data']['create_time'] = date('Y-m-d H:i:s',time());
            }

            $data['data']['pid'] = trim($postData['menupid']);
            $data['data']['route'] = trim($postData['menuroute']);
            $data['data']['sort'] = trim($postData['menusort']);
            $data['data']['icon_class'] = trim($postData['menuicon']);
            $data['data']['display'] = trim($postData['menushow']);
        }else{//处理方法

            $data['table'] = MenuModel::$table[1];
            if($postData['tag'] == 'add'){
                $data['tag'] = 'add';
                $data['data']['pid'] = trim($postData['handleId']);
                $data['data']['create_time'] = date('Y-m-d H:i:s',time());
            }else{
                $data['tag'] = 'edit';
                $data['id'] = trim($postData['handleId']);
            }

            $data['data']['name'] = trim($postData['methodname']);
            $data['data']['route'] = trim($postData['methodroute']);

        }

        $data['data']['operator'] = BaseModel::uid();

        return $data;

    }


}