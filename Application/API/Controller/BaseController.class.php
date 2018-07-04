<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
/**
 * 基类
 * 
 * @author jan
 *
 */
class BaseController extends Controller
{
    
    /**
        @功能:查询账号信息token->phone
        @author:yy
        @date:2018-07-01
    **/
    public function getInfoByToken($token){
        
        $info = self::checkToken($token);
        if ($info['error']!='0') {
            return $info;
        }
        // GET 请求
        // authorizationcode   feixun.SH_7（输入你们的授权码）
        // uid 1230557
        $data['authorizationcode'] = self::authorization();
        $data['uid'] = $info['uid'];
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_phonenumberInfo').'?'.$params;
        $res = json_decode(Curl::curl_get($url),true);
        $res['uid'] = $info['uid'];
        return $res;
    }
    //验证token有效性，返回uid
    public function checkToken($token){
        
        $header = array("Authorization: $token");
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_verifyToken').'?'.$params;
        $info = json_decode(Curl::curl_header_get($url,$header),true);
        return $info; 
    }
    /**
        @功能:获取云账号登录授权码
        @param:yy
        @date:2018-06-30
    **/
    public function authorization(){
        // redirect_uri    回调地址    string  可选，授权回调地址，需要与注册时设置的回调地址保持一致
        // response_type   返回类型    string  这里固定值为code
        // scope    范围权限    string  申请scope权限所需要的参数，如read,write
        $data['client_id'] = C('web_client_id');
        $data['client_secret'] = C('web_client_secret');
        $data['redirect_uri'] = '';
        $data['response_type'] = 'code';
        $data['scope'] = 'read';
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_authorization').'?'.$params;
        $res = json_decode(Curl::curl_get($url),true);
        $authorizationcode = $res['authorizationcode'];
        return $authorizationcode;
    }

    /**
        @功能:返回信息
        @param:yy 
        @date:2018-06-30
    **/
    public function returnMsg($data){
        if (!is_array($data)) {
            $data = json_decode($data,true);
        }
        if ($data['error']=='0') {
            $res['error'] = 0;
        }
        else {
            $res['error'] = 1;
        }
        $res = array_merge($data, $res);
        $res['message'] = C('error_msg')[$data['error']]?C('error_msg')[$data['error']]:$data['message'];
        return json_encode($res,JSON_UNESCAPED_UNICODE);
    }
}