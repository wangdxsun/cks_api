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
class ProductController extends Controller
{


     public function _initialize()
        {
            EntryController::index();
        }


    //获取电子K码，云盘等平台调我们，给产品加上K码
    public function getKcode()
    {
        //EntryController::index();
        if(IS_POST){
            $phone = $_POST["phone"];
            $channel = $_POST["channel"];
            $order_no = $_POST["order_no"];
            if(M("relation")->where(["orderId"=>$order_no])->select()){
                exit(json_encode(array("status" => false, "message" => "该订单已经存在")));
            }
            $products = json_decode($_POST["products"],true);

            //判断金额料号金额金额是否大于总的k码价值
            foreach ($products as $k => $v) {
                //$where["channel1"] = $channel;
                /*if($v["sn"]){
                    $where["sn"]=$v["sn"];
                }*/
                $where["im_pnumber"] = $v["pnumber"];
                $where["im_model"] = $v["pname"];
                $where["status"] = 0;
                $pmoneys = M("relation")->where($where)->field("pmoney")->select();

                if(empty($pmoneys)){
                    exit(json_encode(array("status" => false, "message" => "找不到合适的产品")));
                }
                foreach ($pmoneys as $kk => $money) {
                    if ($money < $v["money"]) {
                        exit(json_encode(array("status" => false, "message" => "传递产品金额大于实际产品金额")));
                        break;
                    }
                }
                if ($v["money"] <= 0) {
                    unset($products[$k]);
                }
            }
            //传递金额过来
            if ($channel == "TUI") {
                //获取推啥接口
                $response = $this->getTresult($phone, $order_no, $products,$channel);

                self::sendMessages($channel,$order_no);
            } else if ($channel == 'ETH') {
                //获取金额
                $response = $this->getTresult($phone,$order_no, $products,$channel);
            } else {
                $response = $this->getTresult($phone, $order_no, $products,$channel);
                self::sendMessages($channel,$order_no);
            }
            exit(json_encode($response));
           // exit(print_r($response));
        }else{
            exit(json_encode(array("status"=>false,"message"=>"请求方式错误"),JSON_UNESCAPED_UNICODE));
        }

    }

    //获取推啥result
    protected function getTresult($phone, $order_no, $products,$channel)
    {
        $response = array();
        $status_pool=array();

       try{
           //M()->startTrans();
           foreach ($products as $k => $v) {
               $where["status"] = 0;
               $where["im_model"] = $v["pname"];
               $where["im_pnumber"] = $v["pnumber"];
               //$where["money"] = array("gt", 0);

               $data = M("relation")->lock(true)->where($where)->find();

              if(empty($data)){
                   return array("status"=>false,"message"=>"查不到对应K码");
               }
               $id = $data["id"];
               $result["clearcd"] = $data["clearcd"];
               $save["orderid"] = $order_no;
               $save["status"]=1;
               $save["allot_time"]=date("Y-m-d H:i:s",time());
               if($channel=="TUI"){
                   $save["channel3"]="1-2";
               }else if($channel=="ETH"){
                   $save["channel3"]="1-4";
                   $save["sn"]=$v["sn"];
                   $result["secretcd"]=$data["secretcd"];
               }else{
                   $save["channel3"]="YP";
               }

               $result["money"] = $save["money"] = $v["money"];
               $save["rephone"]=$phone;
               $result_status=M("relation")->where(["id" => $id])->save($save);
               //E('新增失败1111');
               array_push($response, $result);
               array_push($status_pool,$result_status);
           }
           foreach($status_pool as $kk=>$vv){
               if($vv===false){
                   M()->rollback();
                   return array("status"=>false,"message"=>"系统发生错误");
               }
           }
           M()->commit();
       }catch (Exception $e){
           M()->rollback();
           return array("status"=>false,"message"=>"系统发生错误");
       }

        $new_response["status"]=true;
        $new_response["phone"]=$phone;
        $new_response["order_no"]=$order_no;
        $new_response["channel"]=$channel;
        $new_response["products"]=$response;

        return $new_response;

    }

    //发送短信，暂时没人调
    public function sendMsg()
    {
        $phone = $_POST["phone"];
        $order_no = $_POST["order_no"];
        $channel=$_POST["channel"];
        if($channel=="TUI"){
            $where["channel3"]="1-2";
        }
        $where["orderid"]=$order_no;
        $where["status"]=1;
        $data = M("relation")->where($where)->field("secretcd,money,pname")->select();
        if(empty($data)){
            exit(json_encode(array("status"=>false,"message"=>"找到不到对应的订单")));
        }
        //发消息接口
        self::sendMessages($channel,$order_no);
        exit(json_encode(array("status"=>true)));
    }


    //DDW点兑换跳他们的页面，所以需要回调我们
    public function dhresult(){
        //exit(json_encode(array("stautus"=>true,"msg"=>"11111111111"),JSON_UNESCAPED_UNICODE));
        $phone=$_POST["phone"];
        $channel=$_POST["channel"];
        $kcode=$_POST["kcode"];
        $status=$_POST["status"];
        $dhtotal=$_POST["dhtotal"];
        $rate=$_POST["rate"];
        $exratio=$_POST["exratio"];
        $chkSnsNo=$_POST["cksSnsNo"];
        $last_return_time=$_POST["last_return_time"];

        if(empty($phone)||empty($channel)||empty($kcode)||empty($dhtotal)||empty($dhtotal)||empty($rate)||empty($exratio)||empty($chkSnsNo)||empty($last_return_time)){
            exit(json_encode(array("status"=>false,"msg"=>"缺少必要字段")));
        }
        if($chkSnsNo!=md5($kcode)){
            exit(json_encode(array("status"=>false,"msg"=>"流水号错误")));
        }

        $result=CommonController::ChangeLog($kcode,$rate,$dhtotal,$phone,$status, '1-3',$exratio,$chkSnsNo,$last_return_time, 1, 3, 'ddw');
        header('Content-Type: application/json');
        if($result){
            exit(json_encode(array("status"=>true,"msg"=>"插入成功")));
        }else{
            exit(json_encode(array("status"=>false,"msg"=>"回调失败")));
        }
    }


    //查看K码状态
    public function getstatus(){
        if(IS_POST){
            $clearcd=$_POST["clearcd"];
            $secretcd=$_POST["secretcd"];
            $where=array();
            if(($clearcd)){
                $where["clearcd"]=$clearcd;
            }
            if(($secretcd)){
                $where["secretcd"]=$secretcd;
            }

            $data=M("relation")->field("status as kstatus,last_return_time,money,channel3,secretcd,im_model as pname,im_pnumber as pnumber,clearcd")->where($where)->find();

            $new_where["secretcd"]=$data["secretcd"];
            $data1=M("use_details")->where($new_where)->field("activate_time,dhtotal,exratio")->find();

            if(!$data){
                exit(json_encode(array("status"=>false,"message"=>"查无数据")));
            }
            unset($data["secretcd"]);
            $data1['status']=true;
            $new_data=array_merge($data,$data1);
            exit(json_encode($new_data));
        }else{
            exit(json_encode(array("status"=>false,"message"=>"请求方式错误")));
        }

    }


    //发送短信接口
     public function sendMessages($channel="TUI",$order_no="80200001"){
         set_time_limit(0);
         $auth=$channel=="TUI"?"feixun*123.SH_9913651":"feixun*123.SH_7070483";

         $channel_name=$channel=="TUI"?"推啥":"云盘";
         $sign="尊敬的用户您好,您通过".$channel_name."获取的K码是";

         $data=M("relation")->field("secretcd,rephone")->where(["orderid"=>$order_no])->select();

         foreach($data as $k=>$v){
             $new_sign=$sign.$v["secretcd"]."请妥善保管,30天内有效";

             $phone=$v['rephone'];
             $senddata["authorizationcode"]=$auth;
             $senddata["isCustom"]='true';
             $senddata["msg"]=$new_sign;
             $senddata["phonenumber"]=$phone;
             $senddata["verificationtype"]=0;
             $url="http://114.141.173.41:48080/v1/verificationCode?".http_build_query($senddata);
             Curl::curl_get($url);
             //sleep(1);
         }
     }

    //改变K码状态接口，后台调
    public function changekcode(){
        $clearcd_str=$_POST["clearcd"];
        $secretcd_str=$_POST["secretcd"];
        $method=$_POST["method"];
        if(empty($clearcd_str)&&empty($secretcd_str)){
            exit(json_encode(array("status"=>false,"message"=>"缺少必要参数")));
        }
        //如果不在1-3之内
        if(!in_array($method,array(1,2,3))){
            exit(json_encode(array("status"=>false,"message"=>"method参数不对")));
        }

        //开始对接接口
        if($clearcd_str){
            $cards=json_decode($clearcd_str,true);
            $where["clearcd"]=array("in",$cards);
            $data=M("relation")->field("clearcd,secretcd,channel3,status,last_return_time")->where($where)->select();
        }
        if($secretcd_str){
            $cards=json_decode($secretcd_str,true);
            $where["secretcd"]=array("in",$cards);
            $data=M("relation")->field("clearcd,secretcd,channel3,status,last_return_time")->where($where)->select();
        }

        foreach($data as $kk=>$vv){
            if(($vv["last_return_time"]!="0000-00-00 00:00:00")){
                if($vv["last_return_time"]<date("Y-m-d H:i:s",time())){
                    exit(json_encode(array("status"=>false,"message"=>"已超过最晚退货时间"),JSON_UNESCAPED_UNICODE));

                }
            }

        }
        //print_r($data);die;
        //获取到data，对data进行遍历
       if(count($data)==1){
           $old_status=$data[0]["status"];
           if(($old_status>=4)&&($old_status<6)){
               exit(json_encode(array("status"=>false,"message"=>"K码状态不对")));
           }else{
               $clearcd=$data[0]["clearcd"];
               $secretcd=$data[0]["secretcd"];
               if($old_status==2||$old_status==3){
                   //如果为2就表示已兑换走正常逻辑，冻结变3解冻变4
                   M()->startTrans();
                   $res=self::chooseMethod($data[0]["channel3"],$clearcd,$secretcd,$method);
                   if($res===false){
                       M()->rollback();
                       exit(json_encode(array("status"=>false,"message"=>"调用接口失败1")));
                   } else{
                       if($method==1){
                           $save["status"]=3;
                       }elseif ($method==2){
                           $save["status"]=2;
                       }else{
                           $save["status"]=4;
                       }
                       $result_relat=M("relation")->where(["clearcd"=>$clearcd])->save($save);
                       $result_result1=M("use_details")->where(["secretcd"=>$secretcd])->save($save);
                       if($result_relat===false || $result_result1===false){
                           M()->rollback();
                           exit(json_encode(array("status"=>false,"message"=>"修改本地接口失败")));
                       }else{
                           M()->commit();
                           exit(json_encode(array("status"=>true,"message"=>"操作成功")));
                       }
                   }
               }else{
                  if($method==1){
                      $status1=6;
                  }else if($method==2){
                      $status1=1;
                  }else{
                      $status1=4;
                  }
                   $status1_result=M("relation")->where(["clearcd"=>$clearcd])->save(["status"=>$status1]);
                   if($status1_result===false){
                       exit(json_encode(array("status"=>false,"message"=>"操作失败")));
                   }else{
                       exit(json_encode(array("status"=>true,"message"=>"操作成功")));
                   }
               }

           }
       }else{
           $status_pool=array();
            foreach($data as $k=>$item){
                if(($item["status"]>=4)&&($item["status"]<6)){
                    array_push($status_pool,false);
                }else{
                    $clearcd=$item["clearcd"];
                    $secretcd=$item["secretcd"];
                    if($item["status"]==1){
                        array_push($status_pool,1);
                    }else{
                        $res=self::chooseMethod($item["channel3"],$clearcd,$secretcd,$method);
                        array_push($status_pool,$res);
                    }

                }
            }
         if(in_array(false,$status_pool)){
             exit(json_encode(array("status"=>false,"message"=>"调用接口失败2")));
         }else{
             $status11_pool=array();
            //开始修改数据
             M()->startTrans();
           foreach($data as $kk=>$vv){
               //开始遍历数据
               if($vv["status"]==1){
                   //如果原先就是1，来判断method
                   if($method==1){
                       $status1=6;
                   }else if($method==2){
                       $status1=1;
                   }else{
                       $status1=4;
                   }
                   $where11["clearcd"]=$vv["clearcd"];
                   $save11["status"]=$status1;
                   $result11=M("relation")->where($where)->save($save11);
                   array_push($status11_pool,$result11);
               }else{
                       if($method==1){
                           $status11=3;
                       }else if($method==2){
                           $status11=2;
                       }else{
                           $status11=4;
                       }
                  $where12["secretcd"]=$vv["secretcd"];
                  $save12["stauts"]=$status11;
                   $result12=M("relation")->where($where12)->save($save12);
                   $result123=M("use_details")->where($where12)->save($save12);
                    array_push($status11_pool,$result12);
                   array_push($status11_pool,$result123);
               }

           }
             if(in_array(false,$status11_pool)){
                 M()->rollback();
                 exit(json_encode(array("status"=>false,"message"=>"调用接口失败2")));
             }else{
                 M()->commit();
                 exit(json_encode(array("status"=>true,"message"=>"调用接口成功")));
             }


         }

       }

    }

    //给后台用，退货后等情况下更新其他平台的状态
    public   function chooseMethod($channel="DDW",$clearcd,$secretcd,$method){
        switch ($channel)
        {
            //ddw
            case "1-3":
                 return self::changeDDW($clearcd,$secretcd,$method);
                break;
            //推啥商城
            case "1-2":
                return self::changeTS($clearcd,$secretcd,$method);
                break;
            //商城
            case "1-1":

                return self::changeMall($clearcd,$secretcd,$method);
                break;
            //以太星球
            case "1-4":
                return self::changeEth($clearcd,$secretcd,$method);
                break;
            //骏和
            case "7-2":
                return self::changeJh($clearcd,$secretcd,$method);
                break;
            //华夏
            case "7-1":
                return self::changeHx($clearcd,$secretcd,$method);
                break;
            default:
                exit(json_encode(array("status"=>false,"message"=>"渠道参数不对")));
        }

    }


    //改变ddw，找呵呵哒对接
    protected  function changeDDW($clearcd, $secretcd, $method){
        $sign = md5("$method&$secretcd&".C('ddw_secret'));
        $data = [
            'actionType' => $method,//actionType:1冻结，2取消冻结，3注销
            'kcode' => $secretcd,
            'sign' => $sign
        ];
        $result = Curl::curl_header_post(C('ddw_url'), $data, 'Content-Type: application/x-www-form-urlencoded');
        $result=json_decode($result, true);
        if($result["err"]>0){
            return false;
        }else{
            return true;
        }
    }

    //改变推啥
    public   function changeTS($clearcd,$secretcd,$method){
       //进行判断
        //$url="http://172.17.44.98:8082/cks/blackDiamond/state";
        $url="https://treceive-service.phi-go.com/cks/blackDiamond/state";
        if($method==1){
            $kcodeState=2;
        }elseif($method==2){
            $kcodeState=1;
        }else{
            $kcodeState=$method;
        }
        $data=M("relation")->where(["clearcd"=>$clearcd,"secretcd"=>$secretcd])->find();
        $arr["cksSnsNo"]=$data["cks_sns_no"];
        $arr["channel"]="TUI";
        $arr["mobile"]=$data["rephone"];
        $arr["kcode"]=$secretcd;
        $arr["kcodeState"]=$kcodeState;
        $arr["timeStamp"]=$time=time();
        $arr["token"]="PHICOMMCKS2018";
        ksort($arr);
        $sign=strtoupper(md5(sha1(http_build_query($arr))));
        $arr["signature"]=$sign;
        unset($arr["token"]);
        $result_str=Curl::curl_post($url,$arr);
        $add["url"]=$url;
        $add["request"]=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $add["response"]=$result_str;
        $add["created_at"]=date("Y-m-d H:i:s",time());
        M("loglist")->add($add);
        //file_put_contents("./Application/Runtime/test.txt",$result_str.'--'.date("Y-m-d H:i:s",time()),FILE_APPEND);
        $result_arr=json_decode($result_str,true);
        //print_r($result_arr);die;
        if($result_arr["err"]>0){
            return false;
        }else{
            return true;
        }


    }

    //改变商城
    public  function changeMall($clearcd,$secretcd,$method){
        if($method==1){
            $url="https://uat.phimall.com/openapi/vcprice/froze";
            $new_method="froze";
        }elseif ($method==2){
            $url="https://uat.phimall.com/openapi/vcprice/unfroze";
            $new_method="unfroze";
        }else{
            $url="https://uat.phimall.com/openapi/vcprice/cancel";
            $new_method="cancel";
        }
        $phone=M("relation")->where(["clearcd"=>$clearcd])->getField("rephone");

        $uids=BaseController::getUidByPhone($phone);

        if($uids["err"]>0){
            return false;
        }
        $uid=$uids["uid"];
        $post_arr["kcode"]=$secretcd;
        $post_arr["uid"]=$uid;
        $post_arr["mobile"]=$phone;
        $post["vmc_param_json"] = $param = json_encode($post_arr);
        $timestamp = time();
        $method = $new_method;
        $sign=md5(md5($timestamp).md5($method).md5($param).C('mall_interface'));

        $header = array("Content-type: application/json;charset=UTF-8", "timestamp:$timestamp", "method:$new_method", "sign:$sign");



        $result_str= Curl::curl_header_post($url, $param, $header);
        $add["url"]=$url;
        $add["request"]=json_encode($post,JSON_UNESCAPED_UNICODE);
        $add["response"]=$result_str;
        $add["created_at"]=date("Y-m-d H:i:s",time());
        M("loglist")->add($add);
        $result_arr=json_decode($result_str,true);

        return $result_arr["status"];

    }

    //改变骏和
    protected  function changeJH($clearcd,$secretcd,$method){
        if($method==1){
            $new_method="freeze";
        }elseif ($method==2){
            $new_method="unfreeze";
        }else{
            $new_method="invalid";
        }
        $postparmas=array("kcode"=>$secretcd,"status"=>$new_method,"statusname"=>"status");
        $result_str=ExGiftController::changeGiftStatus($postparmas,'jh_change_status', 'hxwj_key');

        $add["url"]=C('jh_change_status');
        $add["request"]=json_encode($postparmas,JSON_UNESCAPED_UNICODE);
        $add["response"]=$result_str;
        $add["created_at"]=date("Y-m-d H:i:s",time());
        M("loglist")->add($add);
        $result_arr=json_decode($result_str,true);
        if($result_arr["message"]=="success"){
            return true;
        }else{
            return false;
        }
    }
    //改变华夏万家
    protected  function changeHX($clearcd,$secretcd,$method){
        if($method==1){
            $new_method="freeze";
            $statusName = "freeze";
        }elseif ($method==2){
            $new_method="unfreeze";
            $statusName = "unfreeze";
        }else{
            $new_method="invalid";
            $statusName = "invalid";
        }
        //$url="xxxxx";
        //$key="1111111";
        $postparmas=array("kcode"=>$secretcd,"status"=>$new_method,"statusName"=>$statusName);

        $result_str=ExGiftController::changeGiftStatus($postparmas,'hxwj_change_status','hxwj_key');
        $add["url"]=C('hxwj_change_status');
        $add["request"]=json_encode($postparmas,JSON_UNESCAPED_UNICODE);
        $add["response"]=$result_str;
        $add["created_at"]=date("Y-m-d H:i:s",time());
        M("loglist")->add($add);
        $result_arr=json_decode($result_str,true);
        if($result_arr["message"]=="success"){
            return true;
        }else{
            return false;
        }
    }

}


