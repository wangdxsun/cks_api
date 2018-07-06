<?php
namespace Admin\Controller;
use Admin\Model\RoleModel;
use Admin\Model\BaseModel;

/**
 * 角色管理
 * @author jan
 *
 */
class RoleController extends BaseController
{

    //角色列表
    public function index()
    {
        $this->assign('data', RoleModel::getRoleListData($_GET['page']));
        $this->display();
    }

    //添加角色
    public function addRole()
    {

        $_GET['id'] && $this->assign('roleData', BaseModel::getDbData([

            'table' =>  RoleModel::$table[0],
            'where' => ['id'=>$_GET['id']]

        ],false));

        $this->assign('menus', RoleModel:: menuAndMethodDataSet());

        $this->display();
    }

    //删除角色
    public function deleteRole(){

        $this->delRemind(RoleModel::deleteRole(
            [
                'table' => RoleModel::$table[0],
                'where' => ['id' =>$_POST['id'] ]
            ],
            [
                'table' => RoleModel::$table[1],
                'where' => ['role_id' => $_POST['id']],
                'data' => ['role_id' => 0]
            ]));

    }

    //数据封装
    public function checkPost($postData){

        if($_POST['action'] == 'add'){
            $data['tag'] = 'add';
            $data['data']['create_time'] = date('Y-m-d H:i:s',time());
        }else{
            $data['id'] = $postData['id'];
            $data['tag'] = 'edit';
        }

        $data['table'] = RoleModel::$table[0];
        $data['data']['role_name'] = trim($postData['roleName']);
        $data['data']['role_declare'] = trim($postData['roleDeclare']);
        $data['data']['menu_id'] = rtrim($postData['menuIdStr'],',');
        $data['data']['method_id'] = rtrim($postData['methodIdStr'],',');
        $data['data']['operator'] = BaseModel::uid();

        return $data;
    }



}