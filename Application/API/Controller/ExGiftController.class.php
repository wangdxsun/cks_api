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

    public static $table = ['allot_policy'];

    /**
     * @ Purpose: 礼包兑换金额页面显示
     * @param string $money
     * @return []
     */
    public function inquireUserExRatio($money = 666){

        $resData = [];

        $data = BaseModel::getDbData([
            'table' => self::$table[0],
            'where' => ['cash' => 7]
        ]);

        if($data)

            foreach ($data as $key => $val){
                $val['rate_str'] = $val['id'].':'.$val['exratio'];
                $val['last_rate'] = $val['exratio'];
                $val['change_money'] = $val['exratio'] * $money;
                $resData[$key] = $val;
            }

        return $resData;

    }

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

       //return $this->curlPostSend($paramArr, $source, $key);
        p($this->curlPostSend($paramArr, $source,$key));

    }

    /**
     * @ Purpose: 1.2 礼包推送接口
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = [
     * 'phone' => '13333333333' //手机号
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

        //return $this->curlPostSend($paramArr, $source, $key);
        p($this->curlPostSend($paramArr, $source, $key));
    }

    public  function pushGift1($paramArr, $source, $key){

        //return $this->curlPostSend($paramArr, $source, $key);
        p($this->curlPostSend($paramArr, $source, $key));
    }


    //发送数据
    public function curlPostSend($paramArr, $source, $key){
        //echo json_encode(EncryptSignVerify::sign($paramArr));die;
        return Curl::curl_header_post(
            C($source),
            json_encode(EncryptSignVerify::sign($paramArr, $key)),
            ["content-type: application/json;charset=UTF-8"]
        );
    }


    //test
    public function test(){
        /*$arr = [
          'Phone' =>   '18109069773',
          'Kcodetype' =>   'W2',
          'amount' => '66.66',
        ];*/
        $parmArr = [
            'phone' => '18109069773',
            'kcodeType' => 'N1',
            'kcode' => '??',
            'kcodeSn' => '!!',
            'deviceSn' => 'eee',
            'bingSn' => 'jj',
            'Amount' => '666',
      ];
        //$this->inquireUserExStatus($parmArr, 'hxwj', 'hxkey');
        $this->pushGift($parmArr, 'hxwj1', 'hxkey');
    }


}


