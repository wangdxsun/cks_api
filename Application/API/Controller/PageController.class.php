<?php

namespace API\Controller;

use Think\Controller;
use API\Controller\BaseController;
use API\Controller\EthController;
use API\Controller\MallController;
use Admin\Model\BaseModel;
/**
 * 页面操作类
 * 
 * @author yy
 *
 */
class PageController extends BaseController
{
    //初始操作
    public function _initialize()
    {
        $this->verifyEncryptSign(); 
    }
    //响应前台的请求--验证签名
    public function verifyEncryptSign(){

        //验证身份
        $timeStamp = $_GET['t'];
        $randomStr = $_GET['r'];
        $signature = $_GET['s']; // $signature 客户端请求地址中携带的签名,与服务端生成的签名进行比对

        //根据客户端请求过来的数据生成的签名 与$signature 进行对比
        return $this -> arithmetic($timeStamp,$randomStr) != $signature ? -1 : 100;

    }

    /**
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
     */
    private function arithmetic($timeStamp, $randomStr){

        $arr = [
          'timeStamp' => $timeStamp,
          'randomStr' => $randomStr,
          'token' => C('token')
        ];

        //按照首字母大小写顺序排序
        sort($arr, SORT_STRING);

        //转换成大写
        return strtoupper(md5(sha1(implode($arr))));
    }

    //生成签名
    public function createSignature(){
        //时间戳
        $timeStamp = time();
        //随机字符串
        $randomStr = $this -> createNonceStr();
        //生成签名
        $signature = $this -> arithmetic($timeStamp,$randomStr);
        return $signature;
    }

    //随机生成字符串
    private function createNonceStr($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "z".$str;
    }
    /**
        @功能:前端查询K码，获取可兑换信息
        @author:yy
        @date:2018-07-01
    **/
    public function getChangeInfo(){
        $secretcd = $_POST['secretcd'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $secretcd]
        ];
        
        $res = BaseModel::getDbData($condition, false);
        if (empty($res)) {
            exit(json_encode(array('error' => 1,'message' => 'K码输入错误，信息不存在')));
        }
        //status 状态 0未分配 1已分配 2已兑换 3已冻结 4已注销
        if ($res['status']!=1) {
            exit(json_encode(array('error' => 1,'message' => 'K码不是待使用状态')));
        }
        
        $condition2 = [
            'table' => 'allot_policy',
            'fields' => 'describe',
            'where' => ['cash' => 1]
        ];
        $channel_info = BaseModel::getDbData($condition2);
        foreach ($channel_info as $key => $value) {
            $channel_list[] = $this->getChangeMoney($value['describe'],$res);
        }
        print_r($channel_list);
        exit(json_encode(array('error' => 1,'data' => $channel_list)));
        
    }

    public function getChangeMoney($channel,$res){
        //1兑换渠道策略 兑付策略 1渠道 2出货时间 3激活时间 4客户渠道 5料号 6兑换渠道
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 1, 'describe' => $channel]
        ];
        $channel_list = BaseModel::getDbData($condition2,false); 
        $channel_list['money'] = $res['money']*$channel_list['exratio'];
        $channel_list['rate_str'] = $channel_list['id'].':'.$channel_list['exratio'];

        //2出货时间策略 
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 2]
        ];

        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && $this->compareOperat(strtotime($res['allot_time']), strtotime($info['describe']), $info['operator'])){
            $channel_list['money'] = $channel_list['money']*$info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        
        //3激活时间策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 3]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && $this->compareOperat(strtotime(date('Y-m-d')), strtotime($info['describe']), $info['operator'])){
            $channel_list['money'] = $channel_list['money']*$info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }

        //4客户渠道策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 4, 'describe' => $res['channel2']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $channel_list['money'] = $channel_list['money']*$info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        
        //5料号策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 5, 'describe' => $res['pnumber']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $channel_list['money'] = $channel_list['money']*$info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        return $channel_list;
    }

    public function compareOperat($num_1,$num_2,$operator)
    {
        if (empty($num_1)||empty($num_2)) {
            return false;
        }
        eval("\$value = $num_1 $operator $num_2;");
        return $value?true:false;
    }
    /**
        @功能:获取兑换记录
        @author:yy
        @date:2018-07-01
    **/
    public function getHistoryInfo(){
        $token = $_POST['token'];
        if (empty(isset($token))) {
            exit(json_encode(array('error' => 1,'message' => '账号信息错误，缺少token')));
        }
        $info = $this->getInfoByToken($token);
        if ($info['error']!=0) {
            exit(json_encode($info));
        }
        print_r($info);
        $page = $_POST['page'];
        $data = BaseModel::getListData([
            'table'=>'use_details',
            'where' => ['atvphone' => $info['phone']],
            'order' => 'activate_time',
            'pnum' => 20,
            'page' => $page = !empty($page) ? $page : 0
        ]);
        exit(json_encode(array('error' => 0,'data'=>$data)));
    }

    /**
        @功能:点击兑换
        @author:yy
        @date:2018-07-01
    **/
    public function exchange(){
        // token   是   身份唯一表示
        // channel 是   渠道
        // kcode   是   K码值，后台在验证是还需要再次查询数据库
        // money   是   K码价值 
        $channel = $_POST['channel'];
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6IjIifQ.eyJ1aWQiOiI5NTc5NTY3OCIsImNvZGUiOiJmZWl4dW4qMTIzLlNIXzQ3MTczODMiLCJ0eXBlIjoiYWNjZXNzX3Rva2VuIiwiaXNzIjoiUGhpY29tbSIsIm5iZiI6MTUzMDUzMzczNCwiZXhwIjoxNTMwNjYzMzM0LCJyZWZyZXNoVGltZSI6IjIwMTgtMDctMDMgMDg6MTU6MzQifQ.9NQJd9K_kmUGliBX9xTIiyB-PkTbwxJLnlFS0uoaVPE";//登录获取的token
        switch ($channel) {
            case '商城':
                $res = MallController::mallChange($token);
                break;
            case '以太星球':
                
                $price1 = 100;//price1  是   k码对应的人民币价值
                $price2 = 200;//price2  是   K码换算成星积分后的价值
                $res = EthController::ethChange($token, $price1, $price2);
                print_r($res);
                # code...
                break;
            default:
                # code...
                break;
        }
    }
}
