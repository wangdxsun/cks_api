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
                'sign' => md5(http_build_query($parmArr).'&key='.C('key')),
                'data' => [$parmArr],
        ];

    }

    /*private static function arr2str($arr){

        foreach ($arr as $key=> $value)
            $strArr[] = $key.'='.$value;

        return implode('&', $strArr);
    }*/

}
