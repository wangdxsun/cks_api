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

    //响应前台的请求--验证签名
    public function verifyEncryptSign(){

        //验证身份
        $timeStamp = $_GET['t'];
        $randomStr = $_GET['r'];
        $signature = $_GET['s']; // $signature 客户端请求地址中携带的签名,与服务端生成的签名进行比对

        //根据客户端请求过来的数据生成的签名 与$signature 进行对比
        return $this -> arithmetic($timeStamp,$randomStr) != $signature ? -1 : 100;

    }

    /**
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
     */
    private function arithmetic($timeStamp, $randomStr){

        $arr = [
          'timeStamp' => $timeStamp,
          'randomStr' => $randomStr,
          'token' => C('token')
        ];

        //按照首字母大小写顺序排序
        sort($arr, SORT_STRING);

        //转换成大写
        return strtoupper(md5(sha1(implode($arr))));
    }

    //生成签名
    public function createSignature(){
        //时间戳
        $timeStamp = time();
        //随机字符串
        $randomStr = $this -> createNonceStr();
        //生成签名
        $signature = $this -> arithmetic($timeStamp,$randomStr);
        return $signature;
    }

    //随机生成字符串
    private function createNonceStr($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "z".$str;
    }

    /**
        @功能:查询账号信息token->phone
        @author:yy
        @date:2018-07-01
    **/
    public function getInfoByToken($token){
        $res = explode('.', $token);
        $userInfo = json_decode(base64_decode($res[1]),true);
        // GET 请求
        // authorizationcode   feixun.SH_7（输入你们的授权码）
        // uid 1230557
        $data['authorizationcode'] = $this->authorization();
        $data['uid'] = $userInfo['uid'];
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_phonenumberInfo').'?'.$params;
        $res = json_decode(Curl::curl_get($url),true);
        return $res;
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
}