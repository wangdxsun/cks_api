<?php
namespace Admin\Controller;
use Admin\Model\SystemKeysModel;
use Admin\Model\BaseModel;

/**
 * sys => key.value
 * @author jan
 *
 */
class SystemKeysController extends BaseController
{

    //待分配K码列表
    public function index()
    {
        $this->assign('data', SystemKeysModel::getSystemKeysListData($_GET['page']));


        $this->display();
    }

    //获取一条记录
    public function getOneSystemKeysData(){

        $this->ajaxReturn(BaseModel::getDbData([

            'table' => SystemKeysModel::$table[0],
            'where' => ['id' => $_POST['id'] ]

        ],false));
    }


    //删除一条记录
    public function deleteSystemKeys(){
        $this->delRemind(BaseModel::delData([
            'table' => SystemKeysModel::$table[0],
            'where' => ['id' => $_POST['id']]
        ]));
    }

    //数据整合
    public function checkPost($postData){

        $pdata = json_decode($postData['data'],true);

        if($pdata['action'] == 'add'){
            $data['tag'] = 'add';
        }else{
            $data['id'] = $pdata['eid'];
            $data['tag'] = 'edit';
        }

        $data['table'] = SystemKeysModel::$table[0];
        $data['data']['key1'] = trim($pdata['key1']);
        $data['data']['key2'] = trim($pdata['key2']);
        $data['data']['value1'] = trim($pdata['value1']);
        $data['data']['value2'] = trim($pdata['value2']);
        $data['data']['value3'] = trim($pdata['value3']);
        $data['data']['node'] = trim($pdata['node']);
        $data['data']['uid'] = BaseModel::uid();

        return $data;

    }

}