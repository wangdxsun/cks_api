<?php

namespace API\Controller;

use Think\Controller;
use API\Controller\BaseController;
use Admin\Model\BaseModel;
/**
 * 页面操作类
 * 
 * @author yy
 *
 */
class PageController extends BaseController
{
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
            exit(json_encode(array('code' => 1,'message' => 'K码输入错误，信息不存在')));
        }
        //status 状态 0未分配 1已分配 2已兑换 3已冻结 4已注销
        if ($res['status']!=1) {
            exit(json_encode(array('code' => 1,'message' => 'K码不是待使用状态')));
        }
        $money = $res['money'];

        //1兑换渠道策略 兑付策略 1渠道 2出货时间 3激活时间 4客户渠道 5料号 6兑换渠道
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 1]
        ];
        $channel_list = BaseModel::getDbData($condition2);
        $channel_list = array_column($channel_list, NULL, 'describe');
        array_walk($channel_list,array($this,"addkey"),array('key1'=>'money','key2'=>'rate_str', 'value1'=>$money));  //数组添加键值  

        //2出货时间策略 
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 2]
        ];

        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && $this->compareOperat(strtotime($res['allot_time']), strtotime($info['describe']), $info['operator'])){
            array_walk($channel_list,array($this,"editkey"),array('key1'=>'money','key2'=>'rate_str','value1'=>$info['id'],'value2'=>$info['exratio']));
        }
        
        //3激活时间策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 3]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info) && $this->compareOperat(strtotime(date('Y-m-d')), strtotime($info['describe']), $info['operator'])){
            array_walk($channel_list,array($this,"editkey"),array('key1'=>'money','key2'=>'rate_str','value1'=>$info['id'],'value2'=>$info['exratio']));
        }

        //4客户渠道策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 4, 'describe' => $res['channel2']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            array_walk($channel_list,array($this,"editkey"),array('key1'=>'money','key2'=>'rate_str','value1'=>$info['id'],'value2'=>$info['exratio']));
        }
        
        //5料号策略
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 5, 'describe' => $res['pnumber']]
        ];
        $info = BaseModel::getDbData($condition2,false);
        if(!empty($info)){
            array_walk($channel_list,array($this,"editkey"),array('key1'=>'money','key2'=>'rate_str','value1'=>$info['id'],'value2'=>$info['exratio']));
        }
        
        //6第三方提供比例
        $condition2 = [
            'table' => 'allot_policy',
            'where' => ['cash' => 6]
        ];
        $info = BaseModel::getDbData($condition2);print_r($info);
        if(!empty($info)){
            foreach ($info as $key => $value) {
                if ($channel_list[$value['describe']]) {
                    $channel_list[$value['describe']]['money'] = $channel_list[$value['describe']]['money']*$value['exratio'];
                    $channel_list[$value['describe']]['rate_str'] = $channel_list[$value['describe']]['rate_str'].'-'.$value['id'].':'.$value['exratio'];
                }
            }
        }
        echo M()->getLastSql();
        print_r($res);
        exit(json_encode($channel_list));
        //DDW汇率--待定
    }
    public function addkey(&$val, $key, $param){
        $val[$param['key1']] = $param['value1']*$val['exratio'];
        $val[$param['key2']] = $val['id'].':'.$val['exratio'];
    }
    public function editkey(&$val, $key, $param){
        $val[$param['key1']] = $val[$param['key1']]*$param['value2'];
        $val[$param['key2']] = $val[$param['key2']].'-'.$param['value1'].':'.$param['value2'];
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
        $info = $this->getInfoByToken($token);print_r($info);
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
}
