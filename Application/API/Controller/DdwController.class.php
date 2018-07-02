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
class DdwController extends Controller
{
   public function index(){



   }

    //定时任务更新
   public function getrate(){
       $url="https://accountsymtest.phicomm.com/ddwservice/v1/ddwrate";
       $data_str=file_get_contents($url);
       $data=json_decode($data_str);
       if($data["error"]!=0){
           exit("接口请求错误");
       }
       $rate=$data["data"]["rate"];
       $where["cash"]=6;
       $where["describe"]="ddw";
       $result=M("allot_policy")->where($where)->find();
       if($result){
           //修改
           $id=$result["id"];
           $save["update_time"]=date("Y-m-d H:i:s",time());
           $save["rate"]=$rate;
           M("allot_policy")->where(["id"=>$id])->save($save);

       }else{
           //新增
           $add["cash"]=6;
           $add["describe"]="ddw";
           $add["create_time"]=date("Y-m-d H:i:s",time());
           $add["rate"]=$rate;
           M("allot_policy")->add($add);


       }

   }

}


