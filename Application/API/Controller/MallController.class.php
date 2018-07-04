<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * 与商城接口对接类
 * 
 * @author yzw
 *
 */
class MallController extends Controller
{
    //拼接商城加密方法,输入参数，直接return sign
    protected  function entry($timestamp,$method,$param){
        return md5(md5($timestamp).md5($method).md5($param).C('mall_interface'));
    }

    /**
        @功能:商城兑换
        @author:yy
        @date:2018-07-02
    **/
    public function mallChange($token,$kcode,$sku_bn,$amount,$radio){
        //验证token 获取手机号等信息
        $user_info = BaseController::getInfoByToken($token);
        if ($user_info['error']!=0) {
            exit(BaseController::returnMsg($user_info));
        }
        $url = C('mall_url');
        $post["uid"] = $user_info["uid"];
        $post["mobile"] = $user_info["phonenumber"];
        $post["kcode"] = $kcode;
        $post["sku_bn"] = $sku_bn;
        $post["amount"] = $amount;
        $post["radio"] = $radio;
        $post["cks_sns_no"] = md5($kcode);
 
        $post_data["vmc_param_json"] = $param = json_encode($post);
        $timestamp = time();
        $method = "exchange";
        $sign = self::entry($timestamp, $method, $param);

        $header = array("Content-type: application/json;charset=UTF-8", "timestamp:$timestamp", "method:$method", "sign:$sign");
        $result = Curl::curl_header_post($url, $param, $header);
        $result = json_decode($result, true);
        if ($result['status']) {
            //更新K码状态、明显、log


            $res = array('error' => 0, 'message' => $result['message']);
        }
        else{
            //更新K码状态、明显、log

            $res = array('error' => 1, 'message' => $result['message']);
        }
        return $res;
    }


}


