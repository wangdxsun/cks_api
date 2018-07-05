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
        $gift_data = $this->inquireUserExRatio($res['money']);

        exit(BaseController::returnMsg(array('error' => '0','code_data' => $res,'data' => $channel_list,'gift_data' => $gift_data)));
        
    }
    //$res 关系表单条k码数据 tag: 1：商城 2：推啥 3：DDW 4：以太星球
    public function getChangeMoney($tag,$res){
        //1兑换渠道策略 兑付策略 1渠道 2出货时间 3激活时间 4客户渠道 5料号 6兑换渠道
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 1, 'tag' => $tag]
        ];
        $channel_list = BaseModel::getDbData($condition2,false); 
        $rate = $channel_list['exratio'];
        $rate_str = $channel_list['id'].':'.$channel_list['exratio'];

        //2出货时间策略 
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 2]
        ];

        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && self::compareOperat(strtotime($res['allot_time']), strtotime($info['describe']), $info['operator'])){
            $rate = $rate * $info['exratio'];
            $rate_str = $rate_str.'-'.$info['id'].':'.$info['exratio'];
        }
        
        //3激活时间策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 3]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && self::compareOperat(strtotime(date('Y-m-d')), strtotime($info['describe']), $info['operator'])){
            $rate = $rate * $info['exratio'];
            $rate_str = $rate_str.'-'.$info['id'].':'.$info['exratio'];
        }

        //4客户渠道策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 4, 'describe' => $res['channel2']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $rate = $rate * $info['exratio'];
            $rate_str = $rate_str.'-'.$info['id'].':'.$info['exratio'];
        }
        
        //5料号策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 5, 'describe' => $res['pnumber']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            $rate = $rate * $info['exratio'];
            $rate_str = $rate_str.'-'.$info['id'].':'.$info['exratio'];
        }

        $channel_list['last_rate'] = $rate;
        $channel_list['rate_str'] = $rate_str;
        $channel_list['change_money'] = $res['money']*$rate;
        $channel_list['channel_unit'] = C('channel_unit')[$channel_list['cash'].'-'.$channel_list['tag']];
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
     * @ Purpose: 礼包兑换金额页面显示
     * @param string $money
     * @return []
     */
    public function inquireUserExRatio($money){

        $data = BaseModel::getDbData([
            'table' => 'allot_policy',
            'where' => ['cash' => 7]
        ]);

        if($data){
            foreach ($data as $key => $val){
                $resData[] = self::getGiftMoney($val['tag'], $money);
            }
        }
        return $resData;
    }

    public function getGiftMoney($tag, $money){
        $data = BaseModel::getDbData([
            'table' => 'allot_policy',
            'where' => ['cash' => 7, 'tag' => $tag]
        ],false);
        $data['rate_str'] = $data['id'].':'.$data['exratio'];
        $data['last_rate'] = $data['exratio'];
        $data['change_money'] = $data['exratio'] * $money;
        $data['channel_unit'] = C('channel_unit')[$data['cash'].'-'.$data['tag']];
        return $data;
    }
    /**
        @功能:获取兑换记录
        @author:yy
        @date:2018-07-01
    **/
    public function getHistoryInfo(){
        $token = $_POST['token'];
        if (empty(isset($token))) {
            exit(BaseController::returnMsg(array('error' => '103')));
        }
        //验证token 获取手机号等信息
        $info = BaseController::getInfoByToken($token);
        if ($info['error']!='0') {
            exit(BaseController::returnMsg($info));
        }
        
        $page = $_POST['page'];
        $data = BaseModel::joinSecListData([
            'table' => 'use_details',
            'fields' => ['use_details.dhtotal','relation.secretcd','relation.money','relation.im_model,allot_policy.describe,use_details.cash,use_details.tag'],
            'joinWhere' => 'LEFT JOIN relation ON use_details.secretcd = relation.secretcd LEFT JOIN allot_policy ON (allot_policy.cash = use_details.cash AND allot_policy.tag = use_details.tag)',
            'where' => ['atvphone' => $info['phonenumber']],
            'order' => 'activate_time',
            'pnum' => 10,
            'page' => $page = !empty($page) ? $page : 0
        ]);

        array_walk($data['data'],function(&$val, $key){
            $val['channel_unit'] = C('channel_unit')[$val['cash'].'-'.$val['tag']]; 
        });
        exit(BaseController::returnMsg(array('error' => '0', 'data'=>$data)));
    }

    /**
        @功能:点击兑换
        @author:yy
        @date:2018-07-01
    **/
    public function butnChange(){
        $token = $_POST['token'];
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $cash = $_POST['cash'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        $kcode_info = BaseModel::getDbData($condition, false);
        //验证token 并获取uid 手机
        $user_info = BaseController::getInfoByToken($token);
        if ($user_info['error']!='0') {
            exit(BaseController::returnMsg($user_info));
        }

        if ($cash==1) {
            $change_info = $this->getChangeMoney($tag,$kcode_info);
            exit(BaseController::returnMsg(array('error' => '0', 'data'=>$change_info)));
        }
        elseif ($cash==7) {
            $gift_info = $this->getGiftMoney($tag, $kcode_info['money']);
            switch ($tag) {
                case 1://'华夏':
                    // * e.g. $parmArr = [
                    // * 'Phone' => 13333333333 //手机号
                    // * 'Kcodetype' => 'S7' //产品型号
                    // * 'amount' => '66.66' //金额
                    // * ];
                    $param = array(
                        'Phone' => $user_info['phonenumber'],
                        'Kcodetype' => $kcode_info['im_model'],
                        'amount' => strval($gift_info['change_money'])
                    );
                    $res = ExGiftController::inquireUserExStatus($param, 'hxwj', 'hxwj_key');
                    //'0000'成功 rescode
                    // 1000 用户不存在或未实名
                    // 2000 k码类型不存在
                    // 4000 data数据有误
                    // 5000 签名不正确
                    $res = json_decode($res, true);
                    $res['error'] = $res['rescode']=='0000'?'0':$res['rescode'];
                    
                    break;
                case 2://'骏和':
                    
                    break;
                default:
                    # code...
                    break;
            }
            exit(BaseController::returnMsg($res));
        }
    }
    /**
        @功能:点击兑换--确认兑换
        @author:yy
        @date:2018-07-01
    **/
    public function exchange(){
        // token   是   身份唯一表示
        // tag     是   渠道代码 tag: 1：商城 2：推啥 3：DDW 4：以太星球
        // kcode   是   K码值，暗码
        // cash    是   策略类别，1：渠道兑付策略 7：礼包平台策略
        $token = $_POST['token'];
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $cash = $_POST['cash'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        $kcode_info = BaseModel::getDbData($condition, false);
        //验证token 并获取uid 手机
        $user_info = BaseController::getInfoByToken($token);
        if ($user_info['error']!='0') {
            exit(BaseController::returnMsg($user_info));
        }
        
        //跟新流水号，变更状态--锁定-to do 

        if ($cash==1) {
            $change_info = $this->getChangeMoney($tag,$kcode_info);
            switch ($tag) {
                case 1://'商城':
                    $sku_bn = $kcode_info['pnumber'];//料号
                    $amount = round($kcode_info['money'], 2);//K码金额
                    $radio = round($change_info['last_rate'], 2);//兑换浮动比例
                    $res = MallController::mallChange($token,$kcode,$sku_bn,$amount,$radio);
                    break;
                case 2://'推啥':
                    $res=TuiController::index("TS",$kcode,$user_info['phonenumber'],round($change_info['last_rate'], 2),1);
                    if ($res['status']) {
                        # code...
                    }
                    break;
                case 3://'DDW':
                    
                    break;
                case 4://'以太星球':
                    $price1 = floatval($kcode_info['money']);//price1  是   k码对应的人民币价值
                    $price2 = floatval($change_info['change_money']);//price2  是   K码换算成星积分后的价值

                    $res = EthController::ethChange($token, $price1, $price2, $kcode);
                    # code...
                    break;
                default:
                    # code...
                    break;  
            }
        }
        elseif ($cash==7) {
            switch ($tag) {
                case 1://'华夏':
                    /**
                     * @ Purpose: 1.2 礼包推送接口
                     * @param [] $parmArr 若参数值为空 不传
                     * e.g. $parmArr = [
                        'phone' =>   '13795000060',
                        'kcodeType' =>   'W2',
                        'kcode' => 'am123',
                        'kcodeSn' => 'mm1234',
                        'deviceSn' => 'sb1234',
                        'bingSn' => 'bd123',
                        'Amount' => '999'
                     * ];
                     * @return []
                     */
                    $param = array(
                        'phone' => '13333333333', //手机号
                        'kcodeType' => 'S7', //产品型号
                        'kcode' => 'am123', //暗码
                        'kcodeSn' => 'mm1234', //明码
                        'deviceSn' => 'sb1234',//设备码
                        'bingSn' => 'bd123',  //绑定码
                        'Amount' => '666'  //礼包金额
                    );
                    $res = ExGiftController::pushGift($param, 'hxwj_push_gift', 'hxwj_key');
                    $res['error'] = $res['rescode']=='0000'?'0':$res['rescode'];
                    // 0000 礼包生成成功
                    // 1000 用户不存在或未实名
                    // 2000 无兑换资格，请先投资兑换相应k码资格
                    // 3000  该k码礼包已生成
                    // 4000   k码类型不存在
                    // 5000  data数据有误
                    // 6000  请求繁忙
                    // 7000 推送礼包失败

                    break;
                case 2://'骏和':
                    
                    break;
                default:
                    # code...
                    break;
            }
        }
        
        print_r($res);
        if ($res['error']==='0') {
            //变更状态--已兑换
        }else{
            //变更状态--已分配
        }
    }

}
