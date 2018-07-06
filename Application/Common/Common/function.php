<?php

/**
 * 合并所有的css和js代码
 * 生成minCssJs url
 * @param string $fileString 用','分割的相对路径  base.css,index.css
 * @return string 绝对路径
 */
function minCssJs($fileString)
{
    $array = explode(',', $fileString);
    foreach ($array as $key => $val) {
        $string .= ',/' . $val;
    }
    $return = C('TMPL_PARSE_STRING.__PUBLIC__') . '/min?f=' . substr($string, 1);
    return $return;
}

/**
 * 输出信息
 */
function showAll()
{
    $argNum = func_num_args();
    $args = func_get_args();
    
    if ($argNum == 0) {
        echo '<pre>';
        var_dump(null);
        echo '</pre>';
    } else {
        for ($i = 0; $i < count($args); $i ++) {
            echo '<pre>';
            var_dump($args[$i]);
            echo '</pre>';
        }
    }
}

/**
 * 输出信息并结束
 */
function showEnd()
{
    $argNum = func_num_args();
    $args = func_get_args();
    
    if ($argNum == 0) {
        echo '<pre>';
        var_dump(null);
        echo '</pre>';
        exit();
    } else {
        for ($i = 0; $i < count($args); $i ++) {
            echo '<pre>';
            var_dump($args[$i]);
            echo '</pre>';
        }
        exit();
    }
}

/**
 *
 * 截取utf-8中文字符串
 * 
 * @param string $sourcestr            
 * @param int $cutlength            
 * @return string
 */
function utf_substr($sourcestr, $cutlength)
{
    $returnstr = '';
    $i = 0;
    $n = 0;
    $str_length = mb_strlen($sourcestr); // 字符串的字节数
    while (($n < $cutlength) and ($i <= $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = Ord($temp_str); // 得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) // 如果ASCII位高与224，
{
            $returnstr = $returnstr . substr($sourcestr, $i, 3); // 根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3; // 实际Byte计为3
            $n ++; // 字串长度计1
        } elseif ($ascnum >= 192) // 如果ASCII位高与192，
{
            $returnstr = $returnstr . substr($sourcestr, $i, 2); // 根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; // 实际Byte计为2
            $n ++; // 字串长度计1
        } elseif ($ascnum >= 65 && $ascnum <= 90) // 如果是大写字母，
{
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; // 实际的Byte数仍计1个
            $n ++; // 但考虑整体美观，大写字母计成一个高位字符
        } else // 其他情况下，包括小写字母和半角标点符号，
{
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; // 实际的Byte数计1个
            $n = $n + 0.5; // 小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($str_length > $i) {
        $returnstr = $returnstr . "..."; // 超过长度时在尾处加上省略号
    }
    return $returnstr;
}

/**
 * 检查checkbox是否被全中
 *
 * @param $id
 * @param $ids
 */
function checkBoxChecked($id, $ids, $status = 'checked') {
    if(in_array($id,explode(',',$ids))){
        echo $status;
    }
}

/**
 * 判断管理员是否有Item的权限
 * @param $id
 * @param $ids
 * @return int
 */
function judgeItem($id,$ids) {
    if(in_array($id,explode(',',$ids))){
        return 1;
    }
}


//一天开始
function startTime($t = null){
    $time = $t ?:time();
    return date("Y-m-d H:i:s", mktime(0,0,0,date("m", $time),date("d", $time),date("Y", $time)));
}

//一天结束
function endTime($t = null){
    $time =  $t ? :time();
    return date("Y-m-d H:i:s", mktime(23,59,59,date("m", $time),date("d", $time),date("Y", $time)));
}

//页面跳转
function linkUrl($url){
     if(is_numeric($url))

        echo "href =".U('pageJumpUrl', ['id'=>$url])."  target='_blank'";

     elseif(!is_numeric($url) && !empty($url))

        echo "href =".$url."  target='_blank'";

    else

        echo 'href =javascript: void(0)';

}

function p($arr){
    echo "<pre>";
    print_r($arr);
}


/**

 * 获取服务器端IP地址

 * @return string

 */


function getServerIp(){
    if(isset($_SERVER)){

        if($_SERVER['SERVER_ADDR']){

            $server_ip=$_SERVER['SERVER_ADDR'];

        }else{

            $server_ip=$_SERVER['LOCAL_ADDR'];

        }

    }else{
        $server_ip = getenv('SERVER_ADDR');

    }

    return $server_ip;

}


//是否存在公告
function isNotice($notice){

    return $notice ? true: false;
}

//展示公告
function showNotice($notice){

    $local = C('affixLocal');
    foreach(json_decode($notice,true) as $val){
        echo "<a href='{$local}{$val['downLoadPath']}' >{$val['fileName']}</a><br/>";
    }
}

//网站域名
function webDomain($rtn = false){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    if(!$rtn)
    echo $http_type.$_SERVER['HTTP_HOST'];
    else
        return $http_type.$_SERVER['HTTP_HOST'];
}

//公告类型
function getNoticeCate($cate){

    $noticeCate = C('noticeCate');
    echo $noticeCate[$cate];
}

function getProblemUrl(){
    return U('Home/ProblemSolving/index');
}

//获取单据详情地址
function getBillsDetailUrl($processExecutionId, $taskId){

    echo str_replace("REPLACE02", $taskId, str_replace("REPLACE01",$processExecutionId,C('billsDetailUrl')));

}

?>