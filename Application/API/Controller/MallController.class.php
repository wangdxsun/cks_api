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
        $user_info = BaseController::getInfoByToken($token);print_r($user_info);
        //$user_info = array("uid" => 1103,"phonenumber" => "13771028563");
        $url = C('mall_url');
        $post["uid"] = $user_info["uid"];
        $post["mobile"] = $user_info["phonenumber"];
        $post["kcode"] = $kcode;
        $post["sku_bn"] = $sku_bn;
        $post["amount"] = $amount;
        $post["radio"] = $radio;
        $post["cks_sns_no"] = "1111111";
        print_r($post);
        $post_data["vmc_param_json"] = $param = json_encode($post);
        $timestamp = time();
        $method = "exchange";
        $sign = self::entry($timestamp, $method, $param);

        $header = array("Content-type: application/json;charset=UTF-8", "timestamp:$timestamp", "method:$method", "sign:$sign");
        $result = Curl::curl_header_post($url, $param, $header);//($url,json_encode($post_data));
        print_r($result);

    }


}


