<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * 华夏万家请求类
 * 
 * @author yzw
 *
 */
class ChannelController extends Controller
{

    public function changerate(){
        EntryController::index();
        $channel=$_POST["channel"];
        $exratio=$_POST["exratio"];
        if($channel!="HXWJ"&&$channel!="JH"){
            exit(json_encode(array("status"=>false,"message"=>"渠道参数传递错误")));
        }
        if(empty($exratio)){
            exit(json_encode(array("status"=>false,"message"=>"兑换比例参数传递错误")));
        }
        $newchannel=$channel=="HXWJ"?"7-1":"7-2";

        $where["platform"]=$newchannel;

        $save["policy_value"]=$exratio;
        $result=M("policy")->where($where)->save($save);




        if($result===false){
            exit(json_encode(array("status"=>false,"message"=>"修改汇率失败")));
        }else{
            exit(json_encode(array("status"=>true,"message"=>"修改汇率成功")));
        }

    }

}


