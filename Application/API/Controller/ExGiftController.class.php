<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Common\EncryptSignVerify;
use Admin\Model\BaseModel;
/**
 * 登录类
 * 
 * @author Jan
 *
 */
class ExGiftController extends Controller
{

    /**
     * @ Purpose:1.1 用户信息与兑换资格查询接口
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = [
     * 'Phone' => 13333333333 //手机号
     * 'Kcodetype' => 'S7' //产品型号
     * 'amount' => '66.66' //金额
     * ];
     * @return []
     */
    public function inquireUserExStatus($paramArr, $source, $key){

        return self::curlPostSend($paramArr, $source, $key);

    }

    /**
     * @ Purpose: 1.2 礼包推送接口
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = [
     * 'phone' => 13333333333 //手机号
     * 'kcodeType' => 'S7' //产品型号
     * 'kcode' => 'am123' //暗码
     * 'kcodeSn' => 'mm1234' //明码
     * 'deviceSn' => 'sb1234' //设备码
     * 'bingSn' => 'bd123'  //绑定码
     * 'Amount' => 666  //礼包金额
     * ];
     * @return []
     */
    public  function pushGift($paramArr, $source, $key){

        return self::curlPostSend($paramArr, $source, $key);
    }


    public  function changeGiftStatus($paramArr, $source, $key){

        return self::curlPostSend($paramArr, $source, $key);
    }


    //发送数据
    public function curlPostSend($paramArr, $source, $key){
//        echo json_encode(EncryptSignVerify::sign($paramArr, $key));
        return Curl::curl_header_post(
            C($source),
            json_encode(EncryptSignVerify::sign($paramArr, $key)),
            ["content-type: application/json;charset=UTF-8"]
        );
    }


    //test
    public function test(){
        $arr = [
          'Phone' =>   '13795000060',
          'Kcodetype' =>   'W2',
          'amount' => '999'
        ];
        p($this->inquireUserExStatus($arr, 'jh', 'hxwj_key'));
    }

    public function testPushGift()
    {
        $arr = [
            'phone' =>   '13795000060',
            'kcodeType' =>   'W2',
            'kcode' => 'am123',
            'kcodeSn' => 'mm1234',
            'deviceSn' => 'sb1234',
            'bingSn' => 'bd123',
            'Amount' => '999'
        ];
        p($this->pushGift($arr, 'jh_push_gift', 'hxwj_key'));
    }

    /**
     * status: invalid 失效，freeze 冻结，解冻 unfreeze
     * statusName
     */
    public function testChangeGiftStatus()
    {
        $arr = [
            'kcode' => 'am123',
            'status' => 'invalid',
            'statusName' => 'invalid'
        ];
        p($this->changeGiftStatus($arr, 'jh_change_status', 'hxwj_key'));
    }

}


