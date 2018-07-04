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
class PageController extends LoginController
{
    /**
        @功能:前端查询K码，获取可兑换信息
        @author:yy
        @date:2018-07-01
    **/
    public function getChangeInfo(){
        $kcode = $_POST['kcode'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        
        $res = BaseModel::getDbData($condition, false);
        if (empty($res)) {
            exit(BaseController::returnMsg(array('error'=>'101')));
        }

        //status 状态 0未分配 1已分配 2已兑换 
        if ($res['status']!=1) {
            exit(BaseController::returnMsg(array('error'=>'102')));
        }
        
        $condition2 = [
            'table' => 'allot_policy',
            'fields' => 'tag',
            'where' => ['cash' => 1]
        ];
        $channel_info = BaseModel::getDbData($condition2);
        foreach ($channel_info as $key => $value) {
            $channel_list[] = $this->getChangeMoney($value['tag'],$res);
        }
        //各个理财包数据
        $gift_data = ExGiftController::inquireUserExRatio($res['money']);

        exit(BaseController::returnMsg(array('error' => 0,'code_data' => $res,'data' => $channel_list,'gift_data' => $gift_data)));
        
    }
    //$res 关系表单条k码数据
    public function getChangeMoney($tag,$res){
        //1兑换渠道策略 兑付策略 1渠道 2出货时间 3激活时间 4客户渠道 5料号 6兑换渠道
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 1, 'tag' => $tag]
        ];
        $channel_list = BaseModel::getDbData($condition2,false); 
        $rate = $channel_list['exratio'];
        $channel_list['rate_str'] = $channel_list['id'].':'.$channel_list['exratio'];

        //2出货时间策略 
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 2]
        ];

        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && self::compareOperat(strtotime($res['allot_time']), strtotime($info['describe']), $info['operator'])){
            $rate = $rate * $info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        
        //3激活时间策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 3]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && self::compareOperat(strtotime(date('Y-m-d')), strtotime($info['describe']), $info['operator'])){
            $rate = $rate * $info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }

        //4客户渠道策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 4, 'describe' => $res['channel2']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $rate = $rate * $info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        
        //5料号策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 5, 'describe' => $res['pnumber']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $rate = $rate * $info['exratio'];
            $channel_list['rate_str'] = $channel_list['rate_str'].'-'.$info['id'].':'.$info['exratio'];
        }
        $channel_list['last_rate'] = $rate;
        $channel_list['change_money'] = $res['money']*$rate;
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
            exit(BaseController::returnMsg(array('error' => 103)));
        }
        //验证token 获取手机号等信息
        $info = BaseController::getInfoByToken($token);
        if ($info['error']!=0) {
            exit(BaseController::returnMsg($info));
        }
        print_r($info);
        $page = $_POST['page'];
        $data = BaseModel::joinSecListData([
            'table' => 'use_details',
            'fields' => ['use_details.activate_time','relation.secretcd','relation.money','relation.im_model'],
            'joinWhere' => 'relation ON use_details.secretcd = relation.secretcd',
            'where' => ['atvphone' => $info['phonenumber']],
            'order' => 'activate_time',
            'pnum' => 20,
            'page' => $page = !empty($page) ? $page : 0
        ]);
        echo M()->getLastSql();
        // $data = BaseModel::getListData([
        //     'table'=>'use_details',
        //     'where' => ['atvphone' => $info['phone']],
        //     'order' => 'activate_time',
        //     'pnum' => 20,
        //     'page' => $page = !empty($page) ? $page : 0
        // ]);
        exit(BaseController::returnMsg(array('error' => 0, 'data'=>$data)));
    }

    /**
        @功能:点击兑换
        @author:yy
        @date:2018-07-01
    **/
    public function exchange(){
        // token   是   身份唯一表示
        // tag      是   渠道代码
        // kcode   是   K码值，后台在验证是还需要再次查询数据库
        $token = $_POST['token'];
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        
        $kcode_info = BaseModel::getDbData($condition, false);
        $change_info = $this->getChangeMoney($tag,$kcode_info);
        print_r($change_info);
        switch ($tag) {
            case 1://'商城':
                $sku_bn = $kcode_info['pnumber'];//料号
                $amount = round($kcode_info['money'], 2);//K码金额
                $radio = round($change_info['last_rate'], 2);//兑换浮动比例
                $res = MallController::mallChange($token,$kcode,$sku_bn,$amount,$radio);
                break;
            case 2://'推啥':
                
                break;
            case 3://'DDW':
                
                break;
            case 4://'以太星球':
                $price1 = floatval($kcode_info['money']);//price1  是   k码对应的人民币价值
                $price2 = floatval($change_info['change_money']);//price2  是   K码换算成星积分后的价值

                $res = EthController::ethChange($token, $price1, $price2, $kcode);
                print_r($res);
                # code...
                break;
            default:
                # code...
                break;  
        }
    }

    /**
        @功能:点击兑换--礼包
        @author:yy
        @date:2018-07-03
    **/
    public function giftExchange(){
        // token   是   身份唯一表示
        // tag      是   渠道代码
        // kcode   是   K码值，后台在验证是还需要再次查询数据库
        $token = $_POST['token'];
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        
        $kcode_info = BaseModel::getDbData($condition, false);
        $change_info = $this->getChangeMoney($tag,$kcode_info);
        print_r($change_info);
        switch ($tag) {
            case 1://'华夏':

                break;
            case 2://'骏和':
                
                break;
            default:
                # code...
                break;
        }
    }
}
