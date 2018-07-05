<?php
namespace Admin\Controller;
use Admin\Model\CksManageModel;
use Admin\Model\KcodeProduceModel;
use Admin\Model\SystemKeysModel;
use Admin\Model\BaseModel;

/**
 *K码生成
 * @author jan
 *
 */
class KcodeProduceController extends BaseController
{

    public  $arr = [58, 59, 60, 61, 62, 63, 64, 73, 79, 91, 92, 93, 94, 95, 96, 108, 111];
    public  $type = ['ph' => 8, 'BD' => 8, 'am' => 10 ];
    public  $randStart = 51;
    public  $randEnd = 122;




    //列表
    public function  index(){

        //需要导入的页面显示字段
        $this->assign('kcodeImportFields', SystemKeysModel:: getSystemKeys('kcodeImportFields','key2 asc'));

        //需要导入的比例页面显示
        $this->assign('importRestrictions', SystemKeysModel:: getSystemKeys('importRestrictions','key2 asc'));

        //导入列表
        $this->assign('data', KcodeProduceModel:: getProductImportListData($_GET['page']));


        $this->display();
    }

    //模糊搜索
    public function dimSearch(){

        $this->ajaxReturn(CksManageModel::getDimSearchData(I('post.tag'), I('post.describe')));

    }

    //通过料号获取产品名称
    public function getPnameByPnumber(){

        $this->ajaxReturn(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[0],
            'field' => 'pname',
            'where' => ['pnumber' => I('post.pnumber')]
        ],false));

    }

    //导入
    public function imProductInitialData(){

        $pdata = json_decode($_POST['data'],true);
        //$this->verifyChannelName($pdata['channel_name']);//验证渠道名是否存在
        $this->verifyPnumberByPname($pdata['pnumber'], $pdata['pname']);//验证料号和名称是否对应

        $postData = $this->checkPost($pdata);//待添加数据


        for($i=0; $i<$pdata['number']; $i++){

            //生成k码
            $postData['clearcd'] ='ph'.$this->createKcode('ph');
            $postData['secretcd'] =$this->createKcode('am');
            if($pdata['pname'] == 'N1' || $pdata['pname'] == 'N1M')
                $postData['hcode'] ='BD'.$this->createKcode('BD');

            $res = BaseModel::addData([
                'table' => KcodeProduceModel::$table[2],
                'data' =>$postData
            ]);

            if(!$res)$this->ajaxReturn(['status' => 0, 'info' => '导入失败']);
            //if(!$res) continue;

        }

        if($res)$this->ajaxReturn(['status' => 1, 'info' => '导入成功']);

    }

    //数据整合
    public function checkPost($pdata){

        $data['im_model'] = $pdata['pname'];
        $data['im_pnumber'] = $pdata['pnumber'];
        $data['im_time'] = date('Y:m:d H:i:s', time());
        $data['im_staff'] = BaseModel::uid();
        $data['money'] = $pdata['money'];
        $data['close_time'] = 999;
        $data['status'] = 0;
        $data['readdress'] = '';
        return $data;
    }

    //验证 渠道 是否存在
    /*public function  verifyChannelName($channelName){

        if(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[1],
            'where' => ['channel_name' => $channelName]
        ]))
            return $channelName;

        else

            $this->ajaxReturn(['status' => 0, 'info' => '渠道名不存在,请重新填写']);

    }*/

    //验证  料号产品名是否对应
    public function  verifyPnumberByPname($pnumber, $pname){

        if(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[1],
            'where' => ['pnumber' => $pnumber , 'pname' => $pname]
        ]))
            return 1;

        else

            $this->ajaxReturn(['status' => 0, 'info' => '料号与产品名没有对应']);


    }

    //k码生成
    public function createKcode($type) {

        $str = '';

        while(1) {

            $rand = rand($this->randStart,$this->randEnd);

            if(!in_array($rand, $this->arr))

                $str .= chr($rand);

            if(strlen($str) == $this->type[$type])

            break;

        }

        return $str;


    }




    public function test(){

        echo $this->createKcode('ph').'<br/>';
        echo $this->createKcode('BD').'<br/>';
        echo $this->createKcode('n1').'<br/>';
    }



}