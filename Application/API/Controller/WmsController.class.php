<?php
namespace API\Controller;

use Think\Controller;
use Admin\Model\BaseModel;
use API\Common\Curl;
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

        $info = array();
        $info['status'] = $data?true:false;
        $info['kstatus'] = $data?intval($data['status']):NULL;
        $info['isExpire'] = $data?((date("Y-m-d H:i:s")>$data['last_return_time'])?0:1):NULL;//如果没查到记录，是否过期返回NULL
        $info['activeTime'] = $data['allot_time'];
        $info['msg'] = $data?NULL:"没有查到k码";

        exit(json_encode($info,JSON_UNESCAPED_UNICODE));


    }

    /*
     * 定时更新华夏万家N1和N1M推送接口
     */
    public function pushN1Code(){
        $url = 'http://114.141.173.61:6064/api/pushN1Code';
        $header = array('Content-Type:application/json','Accept:application/json');
        $data = BaseModel::getDbData(['table'=>'relation','where'=>['im_model'=>array('in',array('N1M','N1')),'hcode_flag'=>0,'status'=>1]]);
        if(empty($data))
            exit;
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
        exit;

    }
}


