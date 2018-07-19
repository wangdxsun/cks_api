<?php

namespace API\Controller;

use Think\Controller;
use API\Controller\BaseController;
use API\Controller\EthController;
use API\Controller\MallController;
use API\Model\BaseModel;
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
    private $redis;
    private $errorTimes;
    private $attemptTimes;
    //初始操作
    public function _initialize()
    {
        parent::_initialize();
        $this->token = $_POST['token'];
        if (empty($this->token)) {
            exit(BaseController::returnMsg(array('error' => '21')));
        }
        //验证token 获取手机号等信息
        $info = BaseController::getInfoByToken($this->token);
        if ($info['error']!='0') {
            exit(BaseController::returnMsg($info));
        }
        $this->user_info = $info;
        $this->errorTimes = 'kcode_error_times_'.$this->user_info['phonenumber'];
        $this->attemptTimes = 'kcode_attempt_times_'.$this->user_info['phonenumber'];
    }
    /**
        @功能:前端查询K码，获取可兑换信息
        @author:yy
        @date:2018-07-01
    **/
    public function getChangeInfo(){
        $this->checkErrorTimes();
        $kcode = $_POST['kcode'];
        $condition = [
            'table' => 'relation',
            'fields' => '*',
            'where' => ['secretcd' => $kcode]
        ];
        
        $res = BaseModel::getDbData($condition, false);
        if (empty($res)) {
            $this->addErrorTimes();
        }

        //status 状态 0未分配 1已分配 2已兑换 
        if ($res['status']!=1) {
            exit(BaseController::returnMsg(array('error'=>'110', 'message' => C('kcdoe_stauts')[$res['status']])));
        }
        
        $condition2 = [
            'table' => 'policy',
            'fields' => 'policy.*,platform.*, policy.id',
            'joinWhere' => 'LEFT JOIN platform ON policy.platform = platform.platform',
            'where' => ['policy_type' => 4,'policy.pnumber' => $res['im_pnumber'], 'policy.status' => 1],
            'order' => 'platform.sort desc'
        ];
        
        $channel_info = BaseModel::joinSecDbData($condition2);

        $channel_list = array();
        $gift_data = array();
        $res['money'] = floor($res['money']);
        foreach ($channel_info as $key => $value) {
            if ($value['platform']=='7-1' || $value['platform']=='7-2') {
                $gift_data[] = $this->getChangeMoney($value, $res);
            }
            else{
                $channel_list[] =  $this->getChangeMoney($value, $res);
            }
        }

        exit(BaseController::returnMsg(array('error' => '0','code_data' => $res,'data' => $channel_list,'gift_data' => $gift_data)));
        
    }

    //$res 关系表单条k码数据 tag: 1：商城 2：推啥 3：DDW 4：以太星球
    public function getChangeMoney($info, $res){
        //4兑换渠道比例
        $last_rate = $info['policy_value'];
        $rate_str = $info['id'].':'.$info['policy_value'];
        $platform = explode('-', $info['platform']);
        if ($info['flag']) {
            $condition2 = [
                'table' => 'policy',
                'where' => ['pnumber' => $res['im_pnumber'], 'status' =>1]
            ];
            $channel_list = BaseModel::getDbData($condition2); 
            $now_time = time();
            foreach ($channel_list as $key => $value) {
                if ($value['policy_type']==1) {
                    $last_rate *= $value['policy_value'];
                    $rate_str .= '-'.$value['id'].':'.$value['policy_value'];
                }
                if ($value['policy_type']==2 && strtotime($res['allot_time'])>=strtotime($value['start_time']) && strtotime($res['allot_time'])<=strtotime($value['end_time'])) {
                    $last_rate *= $value['policy_value'];
                    $rate_str .= '-'.$value['id'].':'.$value['policy_value'];
                }
                if ($value['policy_type']==3 && $now_time>=strtotime($value['start_time']) && $now_time<=strtotime($value['end_time'])) {
                    $last_rate *= $value['policy_value'];
                    $rate_str .= '-'.$value['id'].':'.$value['policy_value'];
                }
                if ($value['policy_type']==5 && $res['channel2']==$value['channel']) {
                    $last_rate *= $value['policy_value'];
                    $rate_str .= '-'.$value['id'].':'.$value['policy_value'];
                }
            }
        }

        $last_rate = sprintf('%.2f', $last_rate);
        $info['last_rate'] = $last_rate;
        $info['rate_str'] = $rate_str;
        $info['cash'] = $platform[0];
        $info['tag'] = $platform[1];
        $info['change_money'] = floor($res['money']*$last_rate*$info['rate']);
        return $info;
    }

    /**
        @功能:获取兑换记录
        @author:yy
        @date:2018-07-01
    **/
    public function getHistoryInfo(){
        $token = $this->token;
        if (empty($token)) {
            exit(BaseController::returnMsg(array('error' => '103')));
        }
        //验证token 获取手机号等信息
        $info = $this->user_info;
        
        $page = $_POST['page'];
        $data = BaseModel::joinSecListData([
            'table' => 'use_details',
            'fields' => ['use_details.dhtotal','use_details.activate_time','relation.secretcd','relation.money','relation.im_model,platform.platform_name','platform.channel_unit'],
            'joinWhere' => 'LEFT JOIN relation ON use_details.secretcd = relation.secretcd LEFT JOIN platform ON platform.platform = use_details.exchannel',
            'where' => ['atvphone' => $info['phonenumber']],
            'order' => 'activate_time',
            'pnum' => 10,
            'page' => $page = !empty($page) ? $page : 0
        ]);

        exit(BaseController::returnMsg(array('error' => '0', 'data'=>$data)));
    }

    /**
        @功能:点击兑换
        @author:yy
        @date:2018-07-01
    **/
    public function butnChange(){
        $this->checkErrorTimes();
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
        if (empty($kcode_info)) {
            $this->addErrorTimes();
        }
        //验证token 并获取uid 手机
        $user_info = $this->user_info;

        //策略信息
        $info = M('policy')
            ->field('policy.*,platform.*, policy.id')
            ->join('platform ON policy.platform = platform.platform')
            ->where(['policy_type' => 4,'policy.pnumber' => $kcode_info['im_pnumber'],'policy.status' => 1,'platform.platform' => $cash.'-'.$tag])
            ->find();
        $change_info = $this->getChangeMoney($info,$kcode_info);

        if ($cash==1) {
            
            $change_info['account_number'] = $user_info['phonenumber'];
            $change_info['is_account'] = '0';
            if($tag==3)
            {
                exit(BaseController::returnMsg(array('error' => '0', 'data'=>array('url' => C('ddw_h5').'?token='.$token.'&kcode='.$kcode))));
            }
            exit(BaseController::returnMsg(array('error' => '0', 'data'=>$change_info)));
        }
        elseif ($cash==7) {
            
            switch ($tag) {
                case 1://'华夏':
                    $param = array(
                        'Phone' => $user_info['phonenumber'],//手机号
                        'Kcodetype' => $kcode_info['im_model'],//产品型号
                        'amount' => strval($kcode_info['money'])//金额
                    );
                    //print_r($param);
                    $res = ExGiftController::inquireUserExStatus($param, 'hxwj', 'hxwj_key');
                    //{"message":"用户不存在或未实名","data":null,"rescode":"1000","error":"1000"}
                    //'0000'成功 rescode 1000 用户不存在或未实名 2000 k码类型不存在 4000 data数据有误 5000 签名不正确
                    $res = json_decode($res, true);
                    if ($res['rescode']=='0000') {
                        $res['data'] = array_merge($change_info, $res['data']);
                        $res['data']['account_number'] = $res['data']['phone'];
                        $res['data']['is_account'] = '0';
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
                    $res['error'] = $res['rescode'];
                    
                    break;
                case 2://'骏和':
                    $param = array(
                        'Phone' => $user_info['phonenumber'],//手机号
                        'Kcodetype' => $kcode_info['im_model'],//产品型号
                        'amount' => strval($kcode_info['money'])//金额
                    );
                    //print_r($param);
                    $res = ExGiftController::inquireUserExStatus($param, 'jh', 'jh_key');
                    //{"message":"用户不存在或未实名","data":null,"rescode":"1000","error":"1000"}
                    $res = json_decode($res, true);
                    if ($res['rescode']=='0000') {
                        $res['data'] = array_merge($change_info, $res['data']);
                        $res['data']['account_number'] = $res['data']['phone'];
                        $res['data']['is_account'] = '0';
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
                    $res['error'] = $res['rescode'];
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
    public function exchange() {
        $user_info = $this->user_info;
        $verificationcode = $_POST['verificationcode'];
        if (empty($verificationcode)) {
            exit(BaseController::returnMsg(array('error'=>'110', 'message' => '验证码不能为空')));
        }
        $info = BaseController::verifyVerificationCode($user_info['phonenumber'], $verificationcode);
        if ($info['error']!='0') {
            exit(BaseController::returnMsg($info));
        }

        $this->checkErrorTimes();
        // token   是   身份唯一表示
        // tag     是   渠道代码 tag: 1：商城 2：推啥 3：DDW 4：以太星球
        // kcode   是   K码值，暗码
        // cash    是   策略类别，1：渠道兑付策略 7：礼包平台策略
        $token = $this->token;
        $tag = $_POST['tag'];
        $kcode = $_POST['kcode'];
        $cash = $_POST['cash'];
        $rate=1;//汇率
        M()->startTrans();//开启事务
        $where['secretcd'] = $kcode;
        $kcode_info = M('relation')->lock(true)->where($where)->find();

        //如果K码不存在，错误次数+1
        if (is_null($kcode_info)) {
            $this->addErrorTimes();
        }

        //判断kcode状态
        if ($kcode_info['status']!=1) {
            exit(BaseController::returnMsg(array('error'=>'110', 'message' => C('kcdoe_stauts')[$kcode_info['status']])));
        }
        
        //变更状态--锁定
        $save_data['status'] = 5;
        $save_data['channel3'] = $cash.'-'.$tag;
        $result = M('relation')->where(["secretcd"=>$kcode])->save($save_data);
    
        if ($result) {
            M()->commit();//事务提交
        }
        else{
            M()->rollback();
            exit(BaseController::returnMsg(array('error'=>'110', 'message' => '系统错误')));
        }
        
        //策略信息
        $info = M('policy')
            ->field('policy.*,platform.*, policy.id')
            ->join('platform ON policy.platform = platform.platform')
            ->where(['policy_type' => 4,'policy.pnumber' => $kcode_info['im_pnumber'],'policy.status' => 1,'platform.platform' => $cash.'-'.$tag])
            ->find();
        $change_info = $this->getChangeMoney($info,$kcode_info);
        //第一大类策略
        if ($cash==1) {
            
            switch ($tag) {
                case 1://'商城':
                    $sku_bn = $kcode_info['im_pnumber'];//料号
                    $amount = round($kcode_info['money'], 2);//K码金额
                    $radio = round($change_info['last_rate'], 2);//兑换浮动比例
                    $res = MallController::mallChange($token,$kcode,$sku_bn,$amount,$radio);
                    break;
                case 2://'推啥':
                    $result = TuiController::index("TS",$kcode,$user_info['phonenumber'],sprintf('%.2f', $change_info['last_rate']),1);
                    
                    if ($result['status']) {
                        $res = array('error' => '0', 'data' => array('last_return_time' => $result['last_return_time']));
                    }
                    else{
                        $res = array('error' => '110');
                    }
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
          
        }
        //第七大类策略
        elseif ($cash==7) {
            switch ($tag) {
                
                case 1://'华夏':
                    $param = array(
                        'phone' => $user_info['phonenumber'],//手机号//手机号
                        'kcodeType' => trim($kcode_info['im_model']), //产品型号
                        'kcode' => trim($kcode_info['secretcd']),//'am123', //暗码
                        'kcodeSn' => trim($kcode_info['clearcd']),//'mm1234', //明码
                        'deviceSn' => trim($kcode_info['sn']),//'sb1234',//设备码
                        'bingSn' => trim($kcode_info['hcode']),//'bd123',  //绑定码
                        'Amount' => strval($change_info['change_money']),//'666'  //礼包金额
                    );
                    //print_r($param);
                    $res = ExGiftController::pushGift($param, 'hxwj_push_gift', 'hxwj_key');
                    $res = json_decode($res,true);
                    $res['data']['last_return_time'] = $res['data']['fristExpireDate'];
                    $res['error'] = $res['rescode']=='0000'?'0':'110';

                    // 0000 礼包生成成功 1000 用户不存在或未实名 2000 无兑换资格，请先投资兑换相应k码资格 3000  该k码礼包已生成 4000   k码类型不存在 5000  data数据有误 // 6000  请求繁忙 // 7000 推送礼包失败

                    break;
                case 2://'骏和':
                    $param = array(
                        'phone' => $user_info['phonenumber'],//手机号//手机号
                        'kcodeType' => trim($kcode_info['im_model']), //产品型号
                        'kcode' => trim($kcode_info['secretcd']),//'am123', //暗码
                        'kcodeSn' => trim($kcode_info['clearcd']),//'mm1234', //明码
                        'deviceSn' => trim($kcode_info['sn']),//'sb1234',//设备码
                        'bingSn' => trim($kcode_info['hcode']),//'bd123',  //绑定码
                        'Amount' => strval($change_info['change_money']),//'666'  //礼包金额
                    );
                    //print_r($param);
                    $res = ExGiftController::pushGift($param, 'jh_push_gift', 'jh_key');
                    //{"rescode":"0000","message":"礼包生成成功","data":{"fristExpireDate":"2018-08-04","name":"李三毛","phone":"13795000061"}}

                    $res = json_decode($res,true);
                    $res['data']['last_return_time'] = $res['data']['fristExpireDate'];
                    $res['error'] = $res['rescode']=='0000'?'0':'110';
                    break;
                default:
                    # code...
                    break;
            }

        }
        if ($res['error']==='0') {
            //变更状态--已兑换
            $result = CommonController::ChangeLog($kcode,$rate,$change_info['change_money'],$user_info['phonenumber'],1,$cash."-".$tag,round($change_info['last_rate'], 2),md5($kcode),$res['data']['last_return_time'],$cash,$tag,$change_info['rate_str']);
            if ($result) {
                $data['error'] = '0';
            }
        }else{
            //变更状态--已分配
            $save_data['status'] = 1;
            $save_data['channel3'] = ' ';
            M('relation')->where(["secretcd"=>$kcode])->save($save_data);
        }
        exit(BaseController::returnMsg($res));
    }

    private function checkErrorTimes()
    {
        $this->redis = new \Redis();
        $redisCon = $this->redis->connect(C('REDIS_HOST'), C('REDIS_PORT'), 1);
        if (!$redisCon) {
            $this->ajaxReturn(['error' => 110, 'message' => 'Redis连接超时']);
        }
        //先判断是不是在黑名单里面
        if ($this->redis->sIsMember('kcode_blacklist', $this->user_info['phonenumber'])) {
            $this->ajaxReturn(['error' => 110, 'message' => '系统检测到您有刷K码的嫌疑，请联系客服']);
        }
        //如果尝试次数过快，直接拉黑
        $attemptTimes = $this->redis->incr($this->attemptTimes);
        $this->redis->expire($this->attemptTimes, 10);
        if ($attemptTimes > 100) {
            $this->redis->sadd('kcode_blacklist', $this->user_info['phonenumber']);
        }
        if (intval($this->redis->get($this->errorTimes)) > 5) {
            $this->ajaxReturn(['error' => 110, 'message' => 'K码连续错误次数过多，请稍后再试']);
        }
    }

    private function addErrorTimes()
    {
        $this->redis->incr($this->errorTimes);
        $this->redis->expire($this->errorTimes, 60 * 60 * 4);
        exit(json_encode(['error' => 110, 'message' => 'K码不存在']));
    }

}
