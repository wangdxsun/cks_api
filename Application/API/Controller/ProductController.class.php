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


   /*  public function _initialize()
        {
            EntryController::index();
        }*/


    //获取电子K码
    public function getKcode()
    {
        if(IS_POST){
            $phone = $_POST["phone"];
            $channel = $_POST["channel"];
            $order_no = $_POST["order_no"];
            $products = json_decode($_POST["products"],true);

            //判断金额料号金额金额是否大于总的k码价值
            foreach ($products as $k => $v) {
                $where["channel1"] = $channel;
                $where["pnumber"] = $v["pnumber"];
                $where["pname"] = $v["pname"];
                $where["status"] = 1;
                $pmoneys = M("relation")->where($where)->field("pmoney")->select();
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
                $response = $this->getTresult($phone, $order_no, $products);
            } else if ($channel == 'ETH') {
                //获取金额
                $response = $this->getEresult($order_no, $products);
            } else {

            }
            exit(json_encode($response));
           // exit(print_r($response));
        }else{
            exit(json_encode(array("status"=>false,"message"=>"请求方式错误"),JSON_UNESCAPED_UNICODE));
        }

    }

    //获取推啥result
    protected function getTresult($phone, $order_no, $products)
    {
        $response = array();
        $status_pool=array();

       try{
           M()->startTrans();
           foreach ($products as $k => $v) {
               $where["status"] = 0;
               $where["pname"] = $v["pname"];
               $where["pnumber"] = $v["pnumber"];
               $where["money"] = array("gt", 0);

               $data = M("relation")->lock(true)->where($where)->find();
               if(empty($data)){
                   return array("status"=>false,"message"=>"查不到对应K码");
               }
               $id = $data["id"];
               $result["clearcd"] = $data["clearcd"];
               $save["orderid"] = $order_no;
               $save["status"]=1;
               $save["channel1"]="TS";
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
        $new_response["channel"]="TUI";
        $new_response["products"]=$response;
        return $new_response;

    }


    //获取ETH result
    protected function getEresult($order_no, $products)
    {
        $response = array();
        foreach ($products as $k => $v) {
            $where["sn"] = $v["sn"];
            $save["orderid"] = $order_no;
            $save["money"] = $v["money"];
            M("relation")->where($where)->save($save);
            $new_result = M("relation")->where($where)->field("clearcd,money,secretcd")->find();
            array_push($response, $new_result);
        }
        return $response;
    }


    //发送短信
    public function sendMsg()
    {
        $phone = $_POST["phone"];
        $order_no = $_POST["order_no"];
        $data = M("relation")->where(["orderid" => $order_no, "status" => 1])->field("secretcd,money,pname")->select();
        //发消息接口

        exit(json_encode(array("status"=>true)));
    }



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

        $result=CommonController::ChangeLog($kcode,$rate,$dhtotal,$phone,$status,$channel,$exratio,$chkSnsNo,$last_return_time);
        if($result){
            exit(json_encode(array("status"=>true,"msg"=>"插入成功")));
        }else{
            exit(json_encode(array("status"=>false,"msg"=>"回调失败")));
        }
    }


    //查看K码状态
    public function getstatus(){
        $clearcd=$_POST["clearcd"];
        $secretcd=$_POST["secretcd"];
        if(!empty($clearcd)){
            $where["clearcd"]=$clearcd;
        }
        if(!empty($secretcd)){
            $where["secretcd"]=$secretcd;
        }
       $data=M("relation")->field("status as kstatus")->where($where)->find();
        if(!$data){
            exit(json_encode(array("status"=>false,"message"=>"查无数据")));
        }
        exit(json_encode(array("status"=>true,"kstatus"=>$data["kstatus"])));
    }


    //发送短信接口
     public function sendMessages($channel="TUI",$order_no="80200001"){

         $auth=$channel=="TUI"?"feixun*123.SH_9913651":"";

         $channel_name=$channel=="TUI"?"推啥":"云盘";
         $sign="尊敬的用户您好,您通过".$channel_name."获取的K码是";

         $data=M("relation")->field("secretcd,rephone")->where(["orderid"=>$order_no])->select();

         foreach($data as $k=>$v){
             $new_sign=$sign.$v["secretcd"]."请妥善保管,30天内有效";


             $phone=$v['rephone'];
             //echo "http://114.141.173.41:48080/v1/verificationCode?authorizationcode=$auth&isCustom=true&msg=$sign&phonenumber=$phone&verificationtype=0";
             $url="http://114.141.173.41:48080/v1/verificationCode?authorizationcode=$auth&isCustom=true&msg=$new_sign&phonenumber=$phone&verificationtype=0";
              echo $url;
             echo "<hr/>";
             $result=Curl::curl_get($url);
             var_dump($result);
         }


     }

    //改变K码状态接口
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
            $data=M("relation")->field("clearcd,secretcd,channel1,status")->where($where)->select();
        }
        if($secretcd_str){
            $cards=json_decode($secretcd_str,true);
            $where["secretcd"]=array("in",$cards);
            $data=M("relation")->field("clearcd,secretcd,channel1,status")->where($where)->select();
        }
        //获取到data，对data进行遍历
       if(count($data)==1){
           if($data[0]["status"]<2){
               exit(json_encode(array("status"=>true,"message"=>"未激活K码，直接退款")));
           }else{
               $clearcd=$data[0]["clearcd"];
               $secretcd=$data[0]["secretcd"];
               M()->startTrans();
               $res=self::chooseMethod($data[0]["channel1"],$clearcd,$secretcd,$method);
               if($res===false){
                   M()->rollback();
                   exit(json_encode(array("status"=>false,"message"=>"调用接口失败")));
               } else{
                   if($method==1){
                       $save["status"]=3;
                   }elseif ($method==2){
                       $save["status"]=2;
                   }else{
                       $save["status"]=4;
                   }
                   $result_relat=M("relation")->where(["clearcd"=>$clearcd])->save($save);
                   if($result_relat===false){
                       M()->rollback();
                       exit(json_encode(array("status"=>false,"message"=>"修改本地接口失败")));
                   }else{
                       M()->commit();
                       exit(json_encode(array("status"=>true,"message"=>"操作成功")));
                   }
               }
           }
       }else{
           $status_pool=array();
            foreach($data as $k=>$item){
                if($item["status"]<2){
                    array_push($status_pool,1);
                }else{
                    $clearcd=$item["clearcd"];
                    $secretcd=$item["secretcd"];
                    $res=self::chooseMethod($item["channel1"],$clearcd,$secretcd,$method);
                    array_push($status_pool,$res);
                }
            }
         if(in_array(false,$status_pool)){
             exit(json_encode(array("status"=>false,"message"=>"调用接口失败")));
         }else{
             M()->startTrans();
             if($method==1){
                 $save["status"]=3;
             }elseif ($method==2){
                 $save["status"]=2;
             }else{
                 $save["status"]=4;
             }
             $result_status=M("relation")->where($where)->save($save);
             if($result_status===false){
                 M()->rollback();
                 exit(json_encode(array("status"=>false,"message"=>"修改数据失败")));
             }else{
                 M()->commit();
                 exit(json_encode(array("status"=>true,"message"=>"修改数据成功")));
             }
         }

       }
        
    }

    public   function chooseMethod($channel="DDW",$clearcd,$secretcd,$method){
        switch ($channel)
        {
            case "DDW":
                 return self::changeDDW($clearcd,$secretcd,$method);
                break;
            case "TS":
                return self::changeTS($clearcd,$secretcd,$method);
                break;
            case "MALL":
                return self::changeTS($clearcd,$secretcd,$method);
                break;
            case "JH":
                return self::changeTS($clearcd,$secretcd,$method);
                break;
            case "HX":
                return self::changeTS($clearcd,$secretcd,$method);
                break;
            default:
                exit(json_encode(array("status"=>false,"message"=>"渠道参数不对")));
        }

    }


    //改变ddw
    protected  function changeDDW($clearcd,$secretcd,$method){

            return "ddw";

    }

    //改变推啥
    protected  function changeTS($clearcd,$secretcd,$method){
        return "ts";


    }

    //改变商城
    protected  function changeMall($clearcd,$secretcd,$method){
        return "mall";

    }

    //改变骏和
    protected  function chanageJH($clearcd,$secretcd,$method){
          return "jh";
    }
    //改变华夏万家
    protected  function changeHX($clearcd,$secretcd,$method){
        return "hx";
    }

}


