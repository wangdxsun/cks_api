<?php

namespace API\Controller;

use Think\Controller;
use API\Controller\BaseController;
use API\Controller\EthController;
use API\Controller\MallController;
use Admin\Model\BaseModel;
use API\Controller\CommonController;
/**
 * 页面操作类
 * 
 * @author yy
 *
 */
class PageController extends LoginController
{   
    public $token = "";
    public $user_info = array();
    //初始操作
    public function _initialize()
    {
        parent::_initialize();
        $this->token = $_POST['token'];
        if (empty(isset($this->token))) {
            exit(BaseController::returnMsg(array('error' => '103')));
        }
        //验证token 获取手机号等信息
        $info = BaseController::getInfoByToken($this->token);
        if ($info['error']!='0') {
            exit(BaseController::returnMsg($info));
        }
        $this->user_info = $info;
    }
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
        $token = $this->token;
        if (empty(isset($token))) {
            exit(BaseController::returnMsg(array('error' => '103')));
        }
        //验证token 获取手机号等信息
        $info = $this->user_info;
        
        $page = $_POST['page'];
        $data = BaseModel::joinSecListData([
            'table' => 'use_details',
            'fields' => ['use_details.dhtotal','use_details.activate_time','relation.secretcd','relation.money','relation.im_model,allot_policy.describe,use_details.cash,use_details.tag'],
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
        $token = $this->token;
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
        $user_info = $this->user_info;

        if ($cash==1) {
            $change_info = $this->getChangeMoney($tag,$kcode_info);
            $change_info['account_number'] = $user_info['phonenumber'];
            $change_info['is_account'] = '0';
            exit(BaseController::returnMsg(array('error' => '0', 'data'=>$change_info)));
        }
        elseif ($cash==7) {
            $gift_info = $this->getGiftMoney($tag, $kcode_info['money']);
            switch ($tag) {
                case 1://'华夏':
                    $param = array(
                        'Phone' => '18109069773',//$user_info['phonenumber'],//手机号
                        'Kcodetype' => 'W2',// $kcode_info['im_model'],//产品型号
                        'amount' => strval($kcode_info['money'])//金额
                    );
                    //print_r($param);
                    $res = ExGiftController::inquireUserExStatus($param, 'hxwj', 'hxwj_key');
                    //{"message":"用户不存在或未实名","data":null,"rescode":"1000","error":"1000"}
                    //'0000'成功 rescode
                    // 1000 用户不存在或未实名
                    // 2000 k码类型不存在
                    // 4000 data数据有误
                    // 5000 签名不正确
                    $res = json_decode($res, true);
                    if ($res['rescode']=='0000') {
                        $res['data'] = array_merge($gift_info, $res['data']);
                        $res['data']['account_number'] = $res['data']['phone'];
                        $res['data']['is_account'] = '0';
                        $res['error'] = '0';
                        if ($res['data']['exchangPlanAmount']) {
                            $plan_info = explode(',', $res['data']['exchangPlanAmount']);
                            foreach ($plan_info as $key => $value) {
                                $res['data']['plan_detail'][] = explode('-', $value)[0];
                            }
                            
                        }
                    }
                    elseif ($res['rescode']=='1000') {
                        $res['data']['is_account'] = '1';
                    }
                    $res['error'] = $res['rescode']=='0000'?'0':'110';
                    
                    break;
                case 2://'骏和':
                    $param = array(
                        'Phone' => '13795000060',//$user_info['phonenumber'],//手机号
                        'Kcodetype' => 'W2',// $kcode_info['im_model'],//产品型号
                        'amount' => strval($kcode_info['money'])//金额
                    );
                    //print_r($param);
                    $res = ExGiftController::inquireUserExStatus($param, 'jh', 'hxwj_key');
                    //{"message":"用户不存在或未实名","data":null,"rescode":"1000","error":"1000"}
                    $res = json_decode($res, true);
                    if ($res['rescode']=='0000') {
                        $res['data'] = array_merge($gift_info, $res['data']);
                        $res['data']['account_number'] = $res['data']['phone'];
                        $res['data']['is_account'] = '0';
                        $res['error'] = '0';
                        if ($res['data']['exchangPlanAmount']) {
                            $plan_info = explode(',', $res['data']['exchangPlanAmount']);
                            foreach ($plan_info as $key => $value) {
                                $res['data']['plan_detail'][] = explode('-', $value)[0];
                            }
                            
                        }
                    }
                    elseif ($res['rescode']=='1000') {
                        $res['data']['is_account'] = '1';
                    }
                    $res['error'] = $res['rescode']=='0000'?'0':'110';
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
        $token = $this->token;
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $cash = $_POST['cash'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        $kcode_info = BaseModel::getDbData($condition, false);
        //判断kcode状态
        if ($kcode_info['status']!=1) {
            # code...
        }
        $user_info = $this->user_info;

        //变更状态--锁定-to do 
        M('relation')->where(["secretcd"=>$kcode])->save(array(['status']=>5));
        //第一大类策略
        if ($cash==1) {
            $rate=1;//汇率
            $change_info = $this->getChangeMoney($tag,$kcode_info);
            switch ($tag) {
                case 1://'商城':
                    $sku_bn = $kcode_info['pnumber'];//料号
                    $amount = round($kcode_info['money'], 2);//K码金额
                    $radio = round($change_info['last_rate'], 2);//兑换浮动比例
                    $res = MallController::mallChange($token,$kcode,$sku_bn,$amount,$radio);
                    break;
                case 2://'推啥':
                    print_r($user_info);echo $kcode.$user_info['phonenumber'].round($change_info['last_rate'],2);
                    $result = TuiController::index("TS",$kcode,$user_info['phonenumber'],sprintf('%.2f', $change_info['last_rate']),1);
                    
                    if ($result['status']) {
                        $res = array('error' => '0', 'data' => array('last_return_time' => $result['last_return_time']));
                    }
                    else{
                        $res = array('error' => '110');
                    }
                    print_r($res);
                    break;
                case 3://'DDW':
                    $rate=1;
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
          
            if ($res['error']==='0') {
                //变更状态--已兑换
                // * $kcode, 暗码值
                //  * $rate=1, 兑换比例
                //  * $dhtotal, 兑换了多少个
                //  * $phone,   手机号
                //  * $status,  状态0 表示成功1 表示失败
                //  * $channel, 兑换通道
                //  * exratio   兑换比例
                $result = CommonController::ChangeLog($kcode,$rate,$change_info['change_money'],$user_info['phonenumber'],1,$cash."-".$tag,round($change_info['last_rate'], 2),md5($kcode),$res['data']['last_return_time'],$cash,$tag,$change_info['rate_str']);
                if ($result) {
                    $data['error'] = '0';
                }
            }else{
                //变更状态--已分配
                M('relation')->where(["secretcd"=>$kcode])->save(array(['status']=>1));
            }
        }
        //第七大类策略
        elseif ($cash==7) {
            $gift_info = $this->getGiftMoney($tag, $kcode_info['money']);
            switch ($tag) {
                
                case 1://'华夏':
                    $param = array(
                        'phone' => '18109069773',//$user_info['phonenumber'],//手机号//手机号
                        'kcodeType' => 'W2',//$kcode_info['im_model'], //产品型号
                        'kcode' => $kcode_info['secretcd'],//'am123', //暗码
                        'kcodeSn' => $kcode_info['clearcd'],//'mm1234', //明码
                        'deviceSn' => $kcode_info['clearcd'],//'sb1234',//设备码
                        'bingSn' => $kcode_info['hcode'],//'bd123',  //绑定码
                        'Amount' => strval($kcode_info['money']),//'666'  //礼包金额
                    );
                    print_r($param);
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
                    $param = array(
                        'phone' => '13795000061',//$user_info['phonenumber'],//手机号//手机号
                        'kcodeType' => 'W2',//$kcode_info['im_model'], //产品型号
                        'kcode' => $kcode_info['secretcd'],//'am123', //暗码
                        'kcodeSn' => $kcode_info['clearcd'],//'mm1234', //明码
                        'deviceSn' => $kcode_info['clearcd'],//'sb1234',//设备码
                        'bingSn' => $kcode_info['hcode'],//'bd123',  //绑定码
                        'Amount' => strval($kcode_info['money']),//'666'  //礼包金额
                    );
                    print_r($param);
                    $res = ExGiftController::pushGift($param, 'jh_push_gift', 'hxwj_key');
                    //{"rescode":"0000","message":"礼包生成成功","data":{"fristExpireDate":"2018-08-04","name":"李三毛","phone":"13795000061"}}
                    $res['error'] = $res['rescode']=='0000'?'0':$res['rescode'];
                    break;
                default:
                    # code...
                    break;
            }
            if ($res['error']==='0') {
                //变更状态--已兑换
                // * $kcode, 暗码值
                //  * $rate=1, 兑换比例
                //  * $dhtotal, 兑换了多少个
                //  * $phone,   手机号
                //  * $status,  状态0 表示成功1 表示失败
                //  * $channel, 兑换通道
                //  * exratio   兑换比例
                $result = CommonController::ChangeLog($kcode,$rate,$change_info['change_money'],$user_info['phonenumber'],1,$cash."-".$tag,round($change_info['last_rate'], 2),md5($kcode),$res['data']['last_return_time'],$cash,$tag,$change_info['rate_str']);
                if ($result) {
                    $data['error'] = '0';
                }
            }else{
                //变更状态--已分配
                M('relation')->where(["secretcd"=>$kcode])->save(array(['status']=>1));
            }
        }
        exit(BaseController::returnMsg($res));
    }

}
