<?php

namespace Admin\Controller;
use Admin\Model\AccountModel;
use Admin\Model\BaseModel;

/**
 * 用于用户的登录验证控制器类
 * 
 * @author jan
 *
 */
class AccountController extends BaseController
{
    /**
     * 账号列表
     */
    public function index()
    {
       $this->assign('data', AccountModel::getAccountListData($_GET['page']));
       $this->assign('roleName', AccountModel::getRoleName());
       $this->display();
    }

    //获取一条账号数据
    public function getOneAccountData(){

        $this->ajaxReturn(BaseModel::joinSecDbData([

            'table'=>AccountModel::$table[0],
            'joinWhere' => 'role ON admin.role_id = role.id',
            'fields' => ['admin.*','role.role_name'],
            'where' => ['admin.id' => $_POST['id']],

        ]));
    }

    //删除账号
    public function deleteAccount(){

        $this->delRemind(BaseModel::delData([
            'table' => AccountModel::$table[0],
            'where' => ['id' => $_POST['id']]
        ]));

    }

    //数据整合
    public function checkPost($postData){

        $pdata = json_decode($postData['data'],true);

        return  $pdata['action'] == 'add'
            ? [
                'table' => AccountModel::$table[0],
                'tag' => 'add',
                'data' => [
                    'reg_time' => date('Y-m-d H:i:s',time()),
                    'role_id' => trim($pdata['roleName']),
                    'uname' => trim($pdata['username']),
                    'nickname' =>  trim($pdata['name']),
                    'operator' =>  BaseModel::uid(),
                    'password' => password_hash(trim($pdata['pwd']), PASSWORD_BCRYPT)
                    ],
                //添加时需要去重
                'distinct' => [
                    'remind' => '用户名已存在',
                    'distWhere' => ['uname' => trim($pdata['username'])],
                    'table' => AccountModel::$table[0]
                ]
            ]
            : [
                'table' => AccountModel::$table[0],
                'id' => $pdata['eid'],
                'tag' => 'edit',
                'data' => [
                    'role_id' => trim($pdata['roleName']),
                    'nickname' =>  trim($pdata['name']),
                    'operator' =>  BaseModel::uid(),
                ],
            ];

    }

}