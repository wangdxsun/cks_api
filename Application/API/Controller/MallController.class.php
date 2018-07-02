<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;

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
        return md5(md5($timestamp).md5($method).md5($param)."vmcshop_vcprice_interface");
    }

    //拼接参数，发送请求
    //$token,$kcode,$value,$amount,$radio
    public function exchange(){
        //$user_info=BaseController::getInfoByToken($token);
        $user_info=array("uid"=>1103,"mobile"=>"13771028563");
        $url="http://localhost/newtest/0630.php";
        $post["uid"]=$user_info["uid"];
        $post["mobile"]=$user_info["mobile"];
        $post["kcode"]=$kcode="1111";
        $post["value"]=$value=200;
        $post["amount"]=$amount=300;
        $post["radio"]=$radio=2.00;
        $post_data["vmc_param_json"]=$param=json_encode($post);
        $timestamp=time();
        $method="exchange";
        $sign=$this->entry($timestamp,$method,$param);
        /*  
        $header["timestamp"]=$timestamp;
        $header["exchange"]=$method;
        $header["sign"]=$sign;*/
        $header=array("Content-type: application/json;charset=UTF-8","timestamp:$timestamp","method:$method","sign:$sign");
        $result=self::curl_oa_post($url,json_encode($post_data));
        print_r($result);

    }

    public static function curl_oa_post($url,$header,$data){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header); //与OA对接需要加application/json
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }


}


