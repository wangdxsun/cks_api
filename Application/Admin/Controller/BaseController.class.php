<?php

namespace Admin\Controller;

use Admin\Model\BaseModel;
use Admin\Model\HelpModel;
use Think\Controller;

/**
 * 基类
 * 
 * @author jan
 *
 */
class BaseController extends Controller
{

    public function __construct() {

        parent::__construct();

        if(empty($_SESSION['adminInfo']) && ACTION_NAME != 'checkUnamePsw') {
            $this->display('login');
            exit;
        }

    }



    //处理添加或修改的入口
    public function access(){

        $controller = __NAMESPACE__.'\\'.CONTROLLER_NAME."controller";//动态控制器

        $this->editRemind(BaseModel::integrityData($this->integrityData((new $controller)->checkPost($_POST))));

    }

    //处理添加和修改的数据整合
    protected function integrityData($data){
        //中间层 添加或者修改是否需要去重
        $data['distinct'] && $this->distRemind([
            'data' => BaseModel::getDbData([ 'table' => $data['distinct']['table'], 'where' => $data['distinct']['distWhere']]),
            'info' => $data['distinct']['remind']
        ]);


        return [
            'tag' => $data['tag'],
            'data' => [
                'table' => $data['table'],
                'where' => ['id' => $data['id']],
                'data' => $data['data']
            ]
        ];
    }

    //编辑和添加提醒
    protected function editRemind($status){

        $status !== false
            ? $this->ajaxReturn(['status'=>1, 'info'=>'操作成功', 'insertId' => $status])
            : $this->ajaxReturn(['status'=>0, 'info'=>'操作失败,请稍后重试']);
    }

    //删除提醒
    protected function delRemind($status){

        $status !== false
            ? $this->ajaxReturn(['status'=>1, 'info'=>'删除成功'])
            : $this->ajaxReturn(['status'=>0, 'info'=>'删除失败,请稍后重试']);

    }

    //数据重复提醒
    protected  function distRemind($remind){

        $remind['data'] && $this->ajaxReturn(['status'=>2, 'info'=>$remind['info']]);

    }

    //上传单个图片
    public function upload(){
         $this->ajaxReturn(HelpModel::upload($_GET['path']));
    }
}