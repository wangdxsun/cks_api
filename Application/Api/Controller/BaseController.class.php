<?php

namespace API\Controller;

use Think\Controller;

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
}