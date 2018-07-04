<?php
namespace API\Controller;

use Think\Controller;
use Admin\Model\BaseModel;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * wms
 *
 * @author mxj
 *
 */
class WmsController extends BaseController
{
    public function getstatus(){
        $params = json_decode(file_get_contents('php://input'), true);
        $clearcd = $params['clearcd'];

        $data = BaseModel::getDbData(['table'=>'relation','where'=>['clearcd'=>$clearcd]],false);
        if(!$data){
            exit(json_encode(array('status'=>false,'msg'=>$clearcd.': k码不存在'),JSON_UNESCAPED_UNICODE));
        }else{
            exit(json_encode(array('status'=>true,'kstatus'=>intval($data['status'])),JSON_UNESCAPED_UNICODE));
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
            exit(json_encode(array('status'=>false,'msg'=>'状态无效或者信息不完整，请检查'),JSON_UNESCAPED_UNICODE));
        }

        $res = BaseModel::saveData(['table'=>'relation','where'=>['clearcd'=>$clearcd],'data'=>['status'=>$status,'update_time'=>date("Y-m-d H:i:s")]]);
        if(!$res){
            exit(json_encode(array('status'=>false,'msg'=>'执行失败'),JSON_UNESCAPED_UNICODE));
        }else{
            exit(json_encode(array('status'=>true,'msg'=>'执行成功'),JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     * 定时更新华夏万家N1和N1M推送接口
     */
    public function pushN1Code(){
        $url = 'http://114.141.173.61:6064/api/pushN1Code';
        $header = array('Content-Type:application/json','Accept:application/json');
        $data = BaseModel::getDbData(['table'=>'relation','where'=>['im_model'=>array('in',array('N1M','N1')),'hcode_flag'=>0,'status'=>1]]);
        $sign = md5(count($data).'phicomm*123');
        $kcodes = array();

        for($i = 0;$i<count($data);$i++){
            $kcodes[$i]['hx_code'] = $data[$i]['clearcd'];
            $kcodes[$i]['hidden_code'] = $data[$i]['hcode'];
            $kcodes[$i]['nassn'] = $data[$i]['sn'];
            $kcodes[$i]['order_no'] = $data[$i]['orderid'];
            $kcodes[$i]['kcode_type'] = $data[$i]['im_model'];
        }

        $postData['sign'] = $sign;
        $postData['kcodes'] = $kcodes;
        $res = json_decode(Curl::curl_header_post($url,json_encode($postData),$header),true);

        if($res['result'] == 200){
            //按照明码将本次查询到的数据的同步状态改为1
            BaseModel::saveData(['table'=>'relation','where'=>['clearcd'=>array('in',array_column($data,'clearcd'))],'data'=>['hcode_flag'=>1]]);
        }

    }
}


