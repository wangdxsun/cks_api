<?php
namespace Admin\Controller;

use Admin\Model\BaseModel;


/**
 * wms
 *
 * @author mxj
 *
 */
class WmsController extends BaseController
{
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
            $productInfo['status'] = 1;
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
                    'where'=>['clearcd'=>array('in',$clearcd[$i]['cardId'])],
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



    //数据导入
    public function import()
    {
        if(!empty($_FILES['excelFile']['tmp_name']))
        {
            $filename = $_FILES['excelFile']['name'];
            $tmp_name = $_FILES['excelFile']['tmp_name'];
            $extend=strrchr ($filename,'.');
            $extendLower = strtolower($extend);

            /*判别是不是.xls和.xlsx文件，判别是不是excel文件*/
            if (($extendLower != ".xls") && ($extendLower != ".xlsx"))
                exit(json_encode(array('result'=>1, 'msg'=>'请上传excel文件'),JSON_UNESCAPED_UNICODE));
            else{

                //存入excel
                $file = $this->initExcel($filename,$tmp_name);
                if(!$file)
                    exit(json_encode(array('result'=>1, 'msg'=>'文件上传失败'),JSON_UNESCAPED_UNICODE));
                set_time_limit(0);
                $res = $this->readExcel($file); //读取excel，将excel数据转换为键值对的数组
                $count = count($res);

                //每次写入1000个数据就停一会儿
                $M = M('relation');
                $M->startTrans();
                for($i = 0;$i<(int)($count/1000)+1;$i++) {
                    $data = array_slice($res,$i*1000,min(1000,$count-$i*1000));
                    $res = BaseModel::addalldata(['table'=>'relation', 'data'=>$data]);
                    if(!$res){
                        $M->rollback();
                        exit(json_encode(array('result'=>1, 'msg'=>'插入失败，请检查表格'),JSON_UNESCAPED_UNICODE));
                    }
                }
                $M->commit();
                set_time_limit(30);
                exit(json_encode(array('result'=>0, 'msg'=>'插入成功，一共插入'.$count.'条数据'),JSON_UNESCAPED_UNICODE));
            }
        }
    }

    protected function initExcel($file,$filetempname){

        $filePath = 'Upload/Excel/';
        //注意设置时区
        $time=date("y-m-d-H-i-s");//去当前上传的时间
        //获取上传文件的扩展名
        $extend=strrchr ($file,'.');
        //上传后的文件名
        $name=$time.$extend;
        $uploadfile=$filePath.$name;//上传后的文件名地址
        $result=move_uploaded_file($filetempname,$uploadfile);//假如上传到当前目录下

        if($result) //如果上传文件成功，就执行导入excel操作
            return $uploadfile;
        else
            return -1;
    }

    protected function readExcel($file){
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.Shared.Date');
        vendor("PHPExcel.PHPExcel.Reader.Excel5");
        vendor("PHPExcel.PHPExcel.Reader.Excel2007");

        $extend = strrchr($file,'.');
        if($extend == '.xls')
            $PHPReader = new \PHPExcel_Reader_Excel5();
        else
            $PHPReader = new \PHPExcel_Reader_Excel2007();

        // 载入文件
        $mylist = array();

        $objPHPExcel = $PHPReader->load($file);
        $objWorksheet = $objPHPExcel->getSheet(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();

        // for ($row = 1;$row <= $highestRow;$row++) //从第一行开始读取数据
        for ($row = 2; $row <= $highestRow; $row++) //从第二行开始读取数据，一般第一行作为名称
        {
            $strs = array();
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $address = $col.$row;
                $val = $objWorksheet-> getCell($address)-> getValue();
                if ($col == 'E') {//指定g列为时间所在列 第5列
                    $strs[$col] = gmdate("Y-m-d H:i:s", \PHPExcel_Shared_Date::ExcelToPHP($val));
                } else {
                    $strs[$col] = $val;
                }

            }
            //$mylist[$row]['id'] = $strs[0];
            $mylist[$row]['clearcd'] = $strs['B'];
            $mylist[$row]['secretcd'] = $strs['C'];
            $mylist[$row]['status'] = $strs['D'];
            $mylist[$row]['im_time'] = $strs['E'];
            $mylist[$row]['pmoney'] = $strs['F'];
            $mylist[$row]['close_time'] = $strs['G'];
            $mylist[$row]['channel1'] = $strs['H'];
            //$mylist[$row]['allot_time'] = "";
            $mylist[$row]['pnumber'] = "";
            $mylist[$row]['pname'] = "";
            $mylist[$row]['jobnu'] = "";
            $mylist[$row]['name'] = "";
            $mylist[$row]['sn'] = "";
            $mylist[$row]['orderid'] = "";
            $mylist[$row]['rename'] = "";
            $mylist[$row]['rephone'] = "";
            $mylist[$row]['readdress'] = "";
            $mylist[$row]['meid'] = "";
            $mylist[$row]['imei1'] = "";
            $mylist[$row]['imei2'] = "";
            $mylist[$row]['mac'] = "";
            $mylist[$row]['factoryid'] = "";
            $mylist[$row]['im_model'] = "";
            $mylist[$row]['im_pnumber'] = "";
            $mylist[$row]['im_staff'] = "";
            $mylist[$row]['channel2'] = "";
            //$mylist[$row]['money'] = "";
        }
        return $mylist;
    }

}


