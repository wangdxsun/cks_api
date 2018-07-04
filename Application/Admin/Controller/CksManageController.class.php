<?php
namespace Admin\Controller;
use Admin\Model\CksManageModel;
use Admin\Model\SystemKeysModel;
use Admin\Model\BaseModel;

/**
 * 角色管理
 * @author jan
 *
 */
class CksManageController extends BaseController
{

    //待分配K码列表
    public function index()
    {
        //$this->assign('data', CksManageModel::getAllocatListData($_GET['page']));
        $this->assign('tag', $_GET['tag']);

        //策略列表
        $this->assign('data', CksManageModel::getPolicyListData($_GET['tag']));
        //dump(CksManageModel::getPolicyListData($_GET['tag']));die;


        //比例
        //$this->assign('ratio', SystemKeysModel:: getSystemKeys('ratio','key2 asc'));

        //策略
        //$this->assign('policy',SystemKeysModel:: getSystemKeys('policy','key2 asc'));

        //获取本次对应策略
        $this->assign('systemKeysValue',SystemKeysModel:: getSystemKeyValue('policy',$_GET['tag'], 'key2'));

        //对应渠道
        if($_GET['tag'] == 1) $this->assign('channel',SystemKeysModel:: getSystemKeys('channel','key2 asc'));

        //操作符
        if($_GET['tag'] == 2 || $_GET['tag'] == 3) $this->assign('operator',SystemKeysModel:: getSystemKeys('operator','value2 desc'));

        //获取客户渠道 or 获取料号
        if($_GET['tag'] == 4 || $_GET['tag'] == 5)$this->assign('render', [
            'data' => BaseModel::getListData(['table'=>CksManageModel::$table[$_GET['tag']]]),
            'key' => $key = $_GET['tag'] == 4 ? 'channel_name' : 'pnumber',
        ]);

        $this->display();
    }

    //获取一条策略
    public function  getOnePolicyData(){
        $policyData = BaseModel::getDbData([

            'table' => CksManageModel::$table[0],
            'where' => ['id' => $_POST['id'] ]

        ],false);

        $this->ajaxReturn([
            'data'=>$policyData,
            'operator' => SystemKeysModel:: getSystemKeyValue('operator', $policyData['operator'], 'key2')
        ]);
    }

    //删除一条记录
    public function deletePolicy(){
        $this->delRemind(BaseModel::delData([
            'table' => CksManageModel::$table[0],
            'where' => ['id' => $_POST['id']]
        ]));
    }

    //add
    /*public function accessPolicy(){

        $pdata = json_decode($_POST['data'],true);

        foreach(SystemKeysModel:: getSystemKeys('channel') as $val){

            foreach(array_keys($pdata) as $va){

                if($val['value2'] == $va) {
                    $data = [
                        'cash'=>$pdata['sign'],
                        'describe' => $val['value1'],
                        'exratio' => $pdata[$val['value2']],
                        'create_time' => date('Y-m-d H:i:s', time()),
                        'operator' => ''
                    ];

                    if(!BaseModel::addData([
                        'table' => CksManageModel::$table[0],
                        'data' => $data
                    ])){
                        $this->ajaxReturn(['status'=>0, 'info'=>'操作失败,请稍后重试']);exit;
                    }
                }

            }

        };

        $this->ajaxReturn(['status'=>1, 'info'=>'操作成功']);


    }*/
    //数据整合
    public function checkPost($postData){
        $pdata = json_decode($postData['data'],true);

        if($pdata['action'] == 'add'){
            $data['tag'] = 'add';
        }else{
            $data['id'] = $pdata['eid'];
            $data['tag'] = 'edit';
        }

        $data['table'] = CksManageModel::$table[0];
        $data['data']['describe'] = trim($pdata['describe']);
        if($pdata['sign'] == 1 || $pdata['sign'] == 7)$data['data']['tag'] = $this->mapping($pdata['describe']);
        $data['data']['exratio'] = trim($pdata['ratio']);
        $data['data']['cash'] = trim($pdata['sign']);
        $data['data']['operator'] = trim($pdata['operator']) ? trim($pdata['operator']) : '=';
        $data['data']['create_time'] = date('Y-m-d H:i:s',time());

        //添加、修改去重规则
        $data['distinct'] = $this->distinct($pdata);

        return $data;
    }

    //前端渠道接口标识
    public function mapping($describe){

        return SystemKeysModel:: getSystemKeyValue('channel',$describe, 'value1')['key2'];

    }

    //去重规则
    public function distinct($pdata){

        //cash 1渠道兑付 4客户渠道 5料号
        if(trim($pdata['sign']) == 1 || trim($pdata['sign']) == 4 || trim($pdata['sign']) == 5){


            $distinctRole = [
                'remind' => '该策略已存在',
                'distWhere' => ['cash' => trim($pdata['sign']), 'describe' => trim($pdata['describe'])],
                'table' => CksManageModel::$table[0]
            ];

            if($pdata['eid'])$distinctRole['distWhere']['id'] = ['neq',$pdata['eid']] ;

            return $distinctRole;
        }


        //cash 2出货时间策略 3激活时间策略
        if((trim($pdata['sign']) == 2 || trim($pdata['sign']) == 3) && $pdata['action'] == 'add') {

            return [
                'remind' => '已存在策略，请进行修改',
                'distWhere' => ['cash' => trim($pdata['sign'])],
                'table' => CksManageModel::$table[0]
            ];
        }


    }

    //模糊搜索
    public function dimSearch(){

        echo
        $render = [];
        $data = BaseModel::getDbData([

            'table' => CksManageModel::$table[$_GET['tag']],
            'fields' => [CksManageModel::$field[$_GET['tag']]],
            'where' => [ CksManageModel::$field[$_GET['tag']] => ['like', "%".$_GET['describe']."%"]  ],

        ]);
        //echo M(CksManageModel::$table[$_GET['tag']])->getLastSql();
        foreach ($data as $val){
           $render[] = [
               'label' => $val[CksManageModel::$field[$_GET['tag']]],
               'value' => 'channel'
           ];
        }

        //p($render);
        echo json_encode($render);

    }


}