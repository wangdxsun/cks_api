<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * 加密类
 * 
 * @author
 *
 */
class EntryController extends Controller
{
    public function index(){
        $arr["timeStamp"]=$timestamp=$_SERVER["HTTP_TIMESTAMP"];
        $arr["randomStr"]=$randomStr=$_SERVER["HTTP_RANDOMSTR"];
        $post_sign=$_SERVER["HTTP_SIGNATURE"];
        $arr["token"]="PHICOMMCKS2018";
       /* if((time()-300)>$timestamp){
            exit(json_encode(array("status"=>false,"message"=>"请求时间过期")));
        }*/
        ksort($arr);
        $str=http_build_query($arr);

        $sign=strtoupper(md5(sha1($str)));

        if($post_sign!=$sign){
            exit(json_encode(array("status"=>false,"message"=>"签名错误")));
        }
        


    }
}


