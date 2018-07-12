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

       $save["rate"]=round(1/$rate,4);
       $where["platform"]="1-3";
       $result=M("policy")->where($where)->save($save);
       if($result===false){
           exit(json_encode(array("status"=>false,"message"=>"修改接口失败")));
       }else{
           exit(json_encode(array("status"=>true,"message"=>"请求接口成功")));
       }

   }

    //获取K码状态
    public function getKcodeStatus(){

        EntryController::index();
        $where["secretcd"]=$kcode=$_POST["kcode"];
        $data=M("relation")->where($where)->field("status,money,im_pnumber as pnumber,im_model as pname")->find();
        //print_r(M()->getLastSql());die;
        $request=PageController::getChangeMoney(3,$data);
        $exratio=$request["last_rate"];


        header('Content-Type: application/json');
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


    //获取pnames
    public function getPnames(){
        EntryController::index();
        $data=M("relation")->field("im_model")->group("im_model")->select();
        $pnames=array();
        foreach($data as $k=>$v){
            if(empty($v["im_model"])){
                continue;
            }
            array_push($pnames,$v["im_model"]);
        }
        exit(json_encode(array("status"=>true,"panmes"=>$pnames)));
    }


}


