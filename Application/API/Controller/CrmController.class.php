<?php
namespace API\Controller;

use Think\Controller;
use API\Model\BaseModel;

/*
 * Crm
 *
 *
 */
class CrmController extends Controller
{
    public function exchange()
    {
        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            exit(json_encode(['status' => 'false', 'msg' => '参数格式不对'], JSON_UNESCAPED_UNICODE));
        }
        if (empty($params['oldClearCd']) || empty($params['newClearCd']) ||empty($params['operator'])||empty($params['model'])|| empty($params['channel2']) || empty($params['channel1'])) {
            exit(json_encode(['status' => 'false', 'msg' => '参数不完整'], JSON_UNESCAPED_UNICODE));
        }
        if($params['operator'] != 'crm'){
            exit(json_encode(['status' => 'false', 'msg' => '请求来源非法'], JSON_UNESCAPED_UNICODE));
        }
        $oldcd = BaseModel::getDbData(['table' => 'relation', 'where' => ['clearcd' => $params['oldClearCd']]], false);
        $newcd = BaseModel::getDbData(['table' => 'relation', 'where' => ['clearcd' => $params['newClearCd']]], false);
        if (!$oldcd) {
            exit(json_encode(['status' => 'false', 'msg' => '旧k码不存在'], JSON_UNESCAPED_UNICODE));
        }
        if ($oldcd['status'] != 4) {
            exit(json_encode(['status' => 'false', 'msg' => '旧k码未注销'], JSON_UNESCAPED_UNICODE));
        }
        if (!$newcd) {
            exit(json_encode(['status' => 'false', 'msg' => '新k码不存在'], JSON_UNESCAPED_UNICODE));
        }
        if ($newcd['status'] != 3) {
            exit(json_encode(['status' => 'false', 'msg' => '新k码不是已冻结状态'], JSON_UNESCAPED_UNICODE));
        }
        if ($newcd['channel2'] != '备货') {
            exit(json_encode(['status' => 'false', 'msg' => '新k码不是来自备货渠道'], JSON_UNESCAPED_UNICODE));
        }
        if (date('Y-m-d H:i:s') > $newcd['last_return_time']) {
            exit(json_encode(['status' => 'false', 'msg' => '新k码已过期'], JSON_UNESCAPED_UNICODE));
        }
        if($oldcd['im_model'] != $params['model']){
            exit(json_encode(['status' => 'false', 'msg' => '旧产品型号不符合'], JSON_UNESCAPED_UNICODE));
        }
        if($newcd['im_model'] != $params['model']){
            exit(json_encode(['status' => 'false', 'msg' => '新产品型号不符合'], JSON_UNESCAPED_UNICODE));
        }

        $M = M('relation');
        $M->startTrans();
        $data['status'] = 1;
        $data['channel2'] = $params['channel2'];
        $data['channel1'] = $params['channel1'];
        if(!empty($params['jobNumber'])&&empty($newcd['jobnu'])){
            $data['jobnu'] = $params['jobNumber'];
        }
        if(!empty($params['jobName'])&&empty($newcd['name'])){
            $data['name'] = $params['jobName'];
        }
        //$res1 = BaseModel::saveData(['table'=>'relation','where'=>['clearcd'=>$params['oldClearCd']],'data'=>['status'=>3]]);
        $res2 = BaseModel::saveData(['table' => 'relation', 'where' => ['clearcd' => $params['newClearCd']], 'data' => $data]);

        if ($res2) {
            $M->commit();
            exit(json_encode(['status' => 'true', 'msg' => '操作成功'], JSON_UNESCAPED_UNICODE));
        } else {
            $M->rollback();
            exit(json_encode(['status' => 'false', 'msg' => '换货成功'], JSON_UNESCAPED_UNICODE));
        }
    }
}