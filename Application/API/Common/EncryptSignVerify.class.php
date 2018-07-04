<?php
/**
 * 华夏、骏和 签名认证
 *
    @author:jan
 **/
namespace API\Common;

class EncryptSignVerify{

    /**
     * @ Purpose:礼包兑换 加密签名
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = ['phone' => 13333333333 ];
     * @return []
     */
    public static function sign($parmArr) {

        ksort($parmArr);

        return [
                'parterCode' => C('parter_code'),
                'signType' => C('sign_type'),
                'sign' => md5(
                    'data='.json_encode($parmArr).
                    '&'
                    .http_build_query([
                        'parterCode' => C('parter_code'),
                        'signType' => C('sign_type'),
                        'key'=>C('key')
                    ])),
                'data' => $parmArr,
        ];

    }
 //需要签名的数据类型
//data={"Kcodetype":"sss","Phone":"13333333333","amount":"66.66"}&parterCode=103&signType=MD5&key=y36smqkfeOHen88SOq9sYOZ4sTkxfv60

    /*private static function arr2str($arr){

        foreach ($arr as $key=> $value)
            $strArr[] = $key.'='.$value;

        return implode('&', $strArr);
    }*/

}
