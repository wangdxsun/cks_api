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
    //获取电子K码
    public function getKcode()
    {
        $phone = $_POST["phone"];
        $channel = $_POST["channel"];
        $order_no = $_POST["order_no"];
        $products = $_POST["products"];

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
    }

    //获取推啥result
    protected function getTresult($phone, $order_no, $products)
    {
        $response = array();
        foreach ($products as $k => $v) {
            $where["status"] = 0;
            $where["pname"] = $v["pname"];
            $where["pnumber"] = $v["pnumber"];
            $where["money"] = array("eq", 0);
            $data = M("relation")->where($where)->find();
            $id = $data["id"];
            $result["clearcd"] = $data["clearcd"];
            $save["orderid"] = $order_no;
            $reuslt["money"] = $save["money"] = $v["money"];
            M("relation")->where(["id" => $id])->save($save);
            array_push($response, $result);
        }
        return $response;

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
            $new_result = M("relation")->where($where)->field("clearcd,money")->find();
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
        //exit(json_encode(array("stautus"=>true,"msg"=>"11111111111")));
        $phone=$_POST["phone"];
        $channel=$_POST["channel"];
        $kcode=$_POST["kcode"];
        $status=$_POST["status"];
        $dhtotal=$_POST["dhtotal"];
        $rate=$_POST["rate"];
        $exratio=$_POST["exratio"];
        if(empty($phone)||empty($channel)||empty($kcode)||empty($dhtotal)||empty($dhtotal)||empty($rate)||empty($exratio)){
            exit(json_encode(array("status"=>false,"msg"=>"缺少必要字段")));
        }
        $result=CommonController::ChangeLog($kcode,$rate,$dhtotal,$phone,$status,$channel,$exratio);
        if($result){
            exit(json_encode(array("status"=>true,"msg"=>"插入成功")));
        }else{
            exit(json_encode(array("status"=>false,"msg"=>"回调失败")));
        }
    }


}


