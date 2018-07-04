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
        //EntryController::index();
        $channel=$_POST["channel"];
        $exratio=$_POST["exratio"];
        if($channel!="华夏万家"&&$channel!="骏和"){
            exit(json_encode(array("status"=>false,"message"=>"渠道参数传递错误")));
        }
        if(empty($exratio)){
            exit(json_encode(array("status"=>false,"message"=>"兑换比例参数传递错误")));
        }
        $tag=$channel=="华夏万家"?1:2;
        $data=M("allot_policy")->where(["cash"=>7,"describe"=>$channel])->find();
        if($data){
            //修改
            $save["update_time"]=date("Y-m-d H:i:s",time());
            $save["exratio"]=$exratio;
            $result=M("allot_policy")->where(["cash"=>7,"describe"=>$channel])->save($save);
        }else{
            //新增
            $add["create_time"]=date("Y-m-d H:i:s",time());
            $add["exratio"]=$exratio;
            $add["cash"]=7;
            $add["tag"]=$tag;
            $add["describe"]=$channel;
            $result=M("allot_policy")->add($add);
        }
        if($result===false){
            exit(json_encode(array("status"=>false,"message"=>"修改汇率失败")));
        }else{
            exit(json_encode(array("status"=>true,"message"=>"修改汇率成功")));
        }

    }

}


