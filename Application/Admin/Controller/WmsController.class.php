<?php
namespace Admin\Controller;

use Admin\Model\BaseModel;
use Admin\Model\RoleModel;

/**
 * wms
 *
 * @author mxj
 *
 */
class WmsController extends BaseController
{
    public function index(){
        $this->display();
    }

    public function allot()
    {
        $params = json_decode(file_get_contents('php://input'), true);
        $params = $this->postCheck($params);

        //对于明码进行查询
        $clearcd = $params['list'];

        $data = array();
        $res = BaseModel::getDbData(
            ['table'=>'relation',
             'where'=>['clearcd'=>array('in',array_column($clearcd,'cardId'))],
            ],true);

        //查询是否有不存在的k码，将不存在的k码去除
        if(count($res) != count($clearcd)){
            $notExistCd = array_values(array_diff(array_column($clearcd,'cardId'),array_column($res,'clearcd')));
            for($i = 0;$i<count($notExistCd);$i++){
                $data[$notExistCd[$i]]='k码无效';
                foreach($clearcd as $k=>$v){
                    if($v['cardId'] == $notExistCd[$i]){
                        unset($clearcd[$k]);
                    }
                }
            }
        }

        //查询k码金额是否小于产品价格
        for($i = 0;$i<count($clearcd);$i++){
            if($clearcd[$i]['kMoney'] > $res[$i]['pmoney'] ){
                $data[$clearcd[$i]['cardId']]='k码金额不能大于产品价格';
            }
        }

        //状态不为0的记录下来
        for($i = 0;$i<count($res);$i++){
            switch($res[$i]['status']){
                case 0:break;
                case 1:$data[$res[$i]['clearcd']]='k码已分配';
                    break;
                case 2: $data[$res[$i]['clearcd']]='k码已激活';
                    break;
                case 3: $data[$res[$i]['clearcd']]='k码已冻结';
                    break;
                case 4: $data[$res[$i]['clearcd']]='k码已注销';
                    break;
                default: $data[$res[$i]['clearcd']]='k码状态未知';
            }
        }

        //记录到错误，直接返回
        if(!empty($data)){
            exit(json_encode(array('result'=>1,
                'message'=>'k码分配失败',
                'data'=>$data
            ),JSON_UNESCAPED_UNICODE));
        }

        //状态为0的进行分配
        $M = M('relation');
        $M->startTrans();
        $total = 0;
        for($i = 0;$i<count($clearcd);$i++){
            $productInfo['money'] = $clearcd[$i]['kMoney'];
            $productInfo['status'] = ($params['info']['channel2'] == '备货')?2:1;
            $productInfo['sn'] = $clearcd[$i]['sn'];
            $productInfo['pnumber'] = $clearcd[$i]['partNumber'];
            $productInfo['pname'] = $clearcd[$i]['productName'];
            $productInfo['meid'] = $clearcd[$i]['meid'];
            $productInfo['imei1'] = $clearcd[$i]['imei1'];
            $productInfo['imei2'] = $clearcd[$i]['imei2'];
            $productInfo['mac'] = $clearcd[$i]['mac'];

            foreach($productInfo as $k=>$v){
                if(empty($v)){
                    $productInfo[$k] = 'NA';
                }
            }
            $res = BaseModel::saveData(
                ['table'=>'relation',
                    'where'=>['clearcd'=>$clearcd[$i]['cardId']],
                    'data'=>array_merge($productInfo,$params['info'])
                ]);
            if($res)
                $total = $total+1;
        }
        if($total == count($clearcd)){
            $M->commit();
            exit(json_encode(array('result'=>0,
                'message'=>'接口调用成功'
            ),JSON_UNESCAPED_UNICODE));
        }else{
            $M->rollback();
            exit(json_encode(array('result'=>1,
                'message'=>'分配时出错，请重试'
            ),JSON_UNESCAPED_UNICODE));
        }
    }

    //对传入数据进行检查
    protected function postCheck($params){
        //检查格式
        if(!is_array($params)){
            exit(json_encode(array('result'=>1, 'msg'=>'请求数据格式出错，请检查'),JSON_UNESCAPED_UNICODE));
        }

        //检查必传数据
        if(empty($params['allotTime'])||empty($params['allotList'])||empty($params['channel'])){
            exit(json_encode(array('result'=>1, 'msg'=>'数据提交不完整，请检查'),JSON_UNESCAPED_UNICODE));
        }

        //检查明码或者k码金额是否完整
        for($i = 0;$i<count($params['allotList']);$i++){
            if(empty($params['allotList'][$i]['cardId'])||empty($params['allotList'][$i]['kMoney'])) {
                exit(json_encode(array('result' => 1, 'msg' => '明码或者k码金额未提交，请检查'),JSON_UNESCAPED_UNICODE));
            }
        }

        $info['allot_time'] = $params['allotTime'];
        $info['channel1'] = $params['channel'];
        $info['jobnu'] = $params['userId'];
        $info['name'] = $params['userName'];
        $info['orderid'] = $params['orderId'];
        $info['rename'] = $params['rename'];
        $info['rephone'] = $params['rephone'];
        $info['readdress'] = $params['readdress'];
        $info['factoryid'] = $params['factoryId'];
        $info['channel2'] = $params['supply'];

        foreach($info as $k=>$v){
            if(empty($v)){
                $info[$k] = '';
            }
        }
        return ['info'=>$info,'list'=>$params['allotList']];
    }


}


