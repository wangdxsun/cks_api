<?php
namespace API\Controller;

use Think\Controller;
use Admin\Model\BaseModel;
use API\Controller\BaseController;
/**
 * wms
 *
 * @author mxj
 *
 */
class WmsController extends Controller
{
    public function getstatus(){
        $params = json_decode(file_get_contents('php://input'), true);
        $clearcd = $params['clearcd'];

        $data = BaseModel::getDbData(['table'=>'relation','where'=>['clearcd'=>$clearcd]],false);
        if(!$data){
            exit(json_encode(array('status'=>false,'msg'=>$clearcd.': unknown kcode')));
        }else{
            exit(json_encode(array('status'=>true,'kstatus'=>intval($data['status']))));
        }

    }

    /*
     *  wms更新k码状态
     *  author: mxj
     */
    public function wchangekcode(){
        $params = json_decode(file_get_contents('php://input'), true);
        $clearcd = $params['clearcd'];
        $status = in_array($params['status'],array(1,3,4))?$params['status']:0; //只能操作1,3,4三个状态，其他状态无效

        if(empty($clearcd)||empty($status)){
            exit(json_encode(array('status'=>false,'msg'=>'error status or not whole information')));
        }

        $res = BaseModel::saveData(['table'=>'relation','where'=>['clearcd'=>$clearcd],'data'=>['status'=>$status]]);
        if(!$res){
            exit(json_encode(array('status'=>false,'msg'=>'failed')));
        }else{
            exit(json_encode(array('status'=>true,'msg'=>'successfully')));
        }
    }
}


