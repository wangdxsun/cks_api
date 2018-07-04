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

    //定时任务更新,完成
   public function getrate(){
       $url="https://accountsymtest.phicomm.com/ddwservice/v1/ddwrate?activityID=2";
       $data_str=file_get_contents($url);
       $data=json_decode($data_str,true);
       if($data["error"]!=0){
           exit("接口请求错误");
       }
       $rate=$data["data"]["rate"];
       $where["cash"]=1;
       $where["describe"]="DDW";
       $result=M("allot_policy")->where($where)->find();
       if($result){
           //修改
           $id=$result["id"];
           $save["update_time"]=date("Y-m-d H:i:s",time());
           $save["rate"]=$rate;
           M("allot_policy")->where(["id"=>$id])->save($save);

       }else{
           //新增
           $add["cash"]=1;
           $add["describe"]="DDW";
           $add["create_time"]=date("Y-m-d H:i:s",time());
           $add["exratio"]=$data["data"]["premium"];
           $add["rate"]=$rate;
           M("allot_policy")->add($add);


       }

   }

    //获取K码状态
    public function getKcodeStatus(){
        EntryController::index();
        $where["secretcd"]=$kcode=$_POST["kcode"];
        $data=M("relation")->where($where)->field("status,money,pnumber,pname")->find();
        $request=PageController::getChangeMoney(3,$data);
        $exratio=$request["last_rate"];


        if(empty($data)){
            exit(json_encode(array("status"=>false,"message"=>"没有找到对应的K码")));
        }else{
            $data["exratio"]=$exratio;
            $data["kstatus"]=$data["status"];
            $data["cksSnsNo"]=md5($kcode);
            unset($data["status"]);
            $reponse["status"]=true;
            $reponse["data"]=$data;
            exit(json_encode($reponse));
        }


    }

    public function getMoney(){
        $kcode="am005";
        $data=M("relation")->where(["secretcd"=>$kcode])->field("status,money")->find();
        $result=PageController::getChangeMoney(3,$data);
         print_r($result["last_rate"]);
    }


}


