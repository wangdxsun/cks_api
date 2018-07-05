<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * 登录类
 * 
 * @author yzw
 *
 */
class EthController extends Controller
{
    /** 
        act 是   cloud_star_points
        token   是   登录获取的token
        price1  是   k码对应的人民币价值
        price2  是   K码换算成星积分后的价值
        kcode   是   k码
    */
    public function ethChange($token, $price1, $price2,$kcode)
    {           
        $data['act'] = C('eth_act');
        $data['token'] = $token;
        $data['price1'] = $price1;
        $data['price2'] = $price2;
        $data['kcode'] = $kcode;
        $cks_sns_no = md5($kcode);
        $data['cks_sns_no'] = $cks_sns_no;

        $url = C('eth_url');
        $md5_str = md5($token.$price1.$price2.$kcode.$cks_sns_no.C('eth_md5_key'));//print_r($data);echo $token.$price1.$price2.C('eth_md5_key');
        $info = Curl::curl_header_post($url,$data,array("cloud-md5: $md5_str"));
        $info = json_decode($info,true);
        if ($info['return_code']=='SUCCESS') {
            //更新K码状态、明显、log


            $res = array('error' => '0', 'message' => '兑换成功');
        }
        else{
            //更新K码状态、明显、log

            $res = array('error' => '110', 'message' => '兑换失败');
        }
        return $res;
    }

}


