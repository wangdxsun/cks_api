<?php
return array(

	//'配置项'=>'配置值'
   'token' => '2fae188df0878bafcd73f16b1a8e0386',
   //各个渠道货币单位：策略表cash-tag
   'channel_unit' => array(
   		'1-1' => 'K值',
   		'1-2' => 'K值',
   		'1-3' => 'DDW',
   		'1-4' => 'DDW',
   		'7-1' => '元',
   		'7-2' => '元'
   	),
    //华夏、骏和 加密key
    'SECRET_KEY' => 'y36smqkfeOHen88SOq9sYOZ4sTkxfv60',

    //云账号域名地址
    'cloud_url' => 'http://114.141.173.41:48080',//'https://accountsym.phicomm.com'
	//K码兑换H5
	'h5_client_id' => '1569234',
	'h5_client_secret' => '6FE32D94BE97DBB120E7B1C54FC0B239',
	//K码兑换WEB
	'web_client_id' => '4717383',
	'web_client_secret' => 'BC36FC28D4CCF1C204CB3A3BBFA83F3B',
    //授权码
    'cloud_authorization' => '/v1/authorization',
    //登录
    'cloud_login' => '/v1/login',
    //注册
    'cloud_account' => '/v1/account',
    //验证码
    'cloud_verificationCode' => '/v1/verificationCode',
    //忘记密码
    'cloud_forgetpassword' => '/v1/forgetpassword',
    //获取用户信息token->info
    'cloud_phonenumberInfo' => '/v1/phonenumberInfo',
    //验证token有效性
    'cloud_verifyToken' =>  "/v1/verifyToken",
    //验收是否可被注册
    'cloud_checkPhonenumber' => '/v1/checkPhonenumber',
    //
    'cloud_uidInfo' => '/v1/uidInfo',

    //错误信息
    'error_msg' => array(
    	'0' => '成功',
    	'1' => '验证码错误',
    	'2' => '验证码过期',
    	'4' => '旧密码错误',
    	'5' => 'token失效',
    	'7' => '用户名不存在',
    	'8' => '密码错误',
    	'9' => 'client_id不存在',
    	'10' => 'client_secret错误',
    	'11' => '授权码错误',
    	'12' => '参数错误',
    	'13' => '获取验证码失败',
    	'14' => '该账户已经存在',
    	'15' => '密码未设置',
    	'21' => 'token错误',
    	'23' => '验证码已使用',
    	'25' => '邮箱已经注册',
    	'30' => '多端登录账户被踢出',
    	'33' => 'username格式错误',
    	'34' => '手机号格式错误',
    	'38' => '验证码请求过快',
    	'40' => '需要进行手机验证码验证',
    	'50' => '服务器异常',
    	'100' => '邮箱或手机填写错误',
    	'101' => 'K码输入错误，信息不存在',
    	'102' => 'K码不是待使用状态',
    	'103' => '账号信息错误，缺少token',
    	//'110'预留给渠道返回各自错误信息
    ),

    //商城api
    'mall_url' => 'http://mall.wzc.dev.wx-mall.xin:33092/openapi/vcprice/exchange',
    'mall_interface' => 'vmcshop_vcprice_interface',
    //推啥api……
    //以太星球
    'eth_url' => 'http://testnottobuy.phi-block.com:18000/api/',//https://www.phi-block.com/api/
    'eth_act' => 'cloud_star_points',
    'eth_md5_key' => '1wSn0kMbpxaDCx',

    //华夏、骏和,固定参数
    'hxwj' => 'http://newtest.wanjiajinfu.com/webAPI/api?service_name=mbm_kcode_activate_info_req',//1.1用户信息与兑换资格查询接口地址
    'hxwj_push_gift' => 'http://newtest.wanjiajinfu.com/webAPI/api?service_name=mbm_kcode_exchange_plans_req',//1.2
    'jh' => 'http://180.167.58.6:8206/CKSKM/QueryUserAndKCode',
    'jh_push_gift' => 'http://180.167.58.6:8206/CKSKM/ActivateKcode',
    'jh_deal_status' => 'http://180.167.58.6:8206/CKSKM/DealKCodeStatus',
    'parter_code' => '103',
    'sign_type' => 'MD5',
    'hxwj_key' => 'y36smqkfeOHen88SOq9sYOZ4sTkxfv60',

);