<?php
return array(
	//'配置项'=>'配置值'
    //接口地址
    'searchUser' => "https://phichat.phicomm.com/index.php/API/Visit/search", //搜索用户
    'devGetBaseInfo' => "172.18.114.67:8080/oa-deploy/workflow/queryFullStaffInfoByStaffId.dho", //个人信息dev
    'testGetBaseInfo' => "https://ssorelease.phicomm.com/workflow/queryFullStaffInfoByStaffId.do",//个人信息test
    'devGetNotice' => "172.18.114.67:8080/oa-deploy/notice/queryNoticeByTypeAndTile.do",//公告dev
    //'testGetNotice' => "https://ssorelease.phicomm.com/notice/queryNoticeByTypeAndTile.do",//公告test
    'testGetNotice' => "https://oa.phicomm.com/notice/queryNoticeByTypeAndTile.do",//公告test
    //'affixLocal' => "172.18.114.67:8080/oa-deploy/dymcform/downloadFileExpand.do?uuid=2c913fb95e136b4c015e16fd92ed0042",//附件localdev
    'affixLocal' => "https://ssorelease.phicomm.com/",//附件localdev


    //单据详情
    'billsDetailUrl' => "https://oarelease.phicomm.com/workflow/doJob.action?taskVo.processExecutionId=REPLACE01&taskVo.taskId=REPLACE02",

    //考勤查询
    'workUrl' => 'https://oarelease.phicomm.com/attendance/indexDaily.action',

    //申请请假
    'applyLeave' => 'https://oarelease.phicomm.com/workflow/createJob.action?taskVo.processDefineId=2c913f9e4906ec2001490c3a680f39f7&command=start',

    //申请调休
    'applyRest' => 'https://oarelease.phicomm.com/workflow/createJob.action?taskVo.processDefineId=2c913fa751f32efe01520f7569624c65&command=start',


    //日历参数
    'calendar' => [
        'server' => 'mail.phicomm.com',
        'startDate' => 'January 01 00:00:00',
        'endDate' => 'December 31 23:59:59'
    ],

    //SSO
    'SSO' => [
        'casHost' => 'ssorelease.phicomm.com',
        'casContext' => '/sso',
        'casPort' => 443,
        'casServer' => webDomain(1),
    ],

    //公告类型
    'noticeCate' => [
        0 =>'Announcement',
        1 => 'IT',
        2=>'HR',
        3=>'ADM',
        4=>'Finance',
        5=>'ISO',
        6=>'College',
        7=>'LU',
        30=>'Phimedia',
    ],

    //footer pesonal modify limits
     'module' => [
        1 => '会议日历',
        2 =>'通讯录',
        3 => '单位代办',
        4 => '假期',
        5 => '调休',
        6 => '考勤'
    ],

     //上传路径
    'HOME_PATH'=>[
        'FACE' => './Uploads/avatar/',
        'ALBUM' => './Uploads/album/',
    ],

    //报修
    'repairs' =>[
        'getCate' => 'http://218.245.64.140:5000/apiWeiXinController.do?findCategoryByParentCode',
        'sendProblem' => 'http://218.245.64.140:5000/apiWeiXinController.do?saveEvent&'
    ],

    //'softwareCenter' => 'http://172.17.192.222/CMApplicationCatalog/#/SoftwareCatalog/FullRefresh/true',


    //index fooot module
    'footModule' =>[

        ['title' => '问题提交', 'url' => webDomain(1).'/index.php/Home/ProblemSolving/index', 'class' =>'wttj'],
        ['title' => '软件中心', 'url' => 'http://172.17.192.222/CMApplicationCatalog/#/SoftwareCatalog/FullRefresh/true', 'class' =>'rjzx'],
        ['title' => '服务介绍', 'url' => '', 'class' =>'fwjs'],
        ['title' => 'IT业务接口人', 'url' => '', 'class' =>'itjk'],
    ],

    //IT Tel
    'itTel' => '80000',
);