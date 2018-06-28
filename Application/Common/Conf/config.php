<?php
return array(
	//'配置项'=>'配置值'
    //数据库配置信息
    'DB_TYPE' => "mysql", // 数据库类型
    'DB_HOST' => "localhost", //
    'DB_NAME' => "page", // 数据库名
    'DB_USER' => "root", // 用户名
    'DB_PWD' => "", // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => "", // 数据库表前缀
    'DB_CHARSET' => "utf8", // 字符集


    'TMPL_EXCEPTION_FILE' => APP_DEBUG ? THINK_PATH.'Tpl/think_exception.tpl' : './Public/html/404.html',
    //'TMPL_EXCEPTION_FILE' => './Public/html/404.html',
    //'TMPL_ACTION_ERROR'   =>  APP_DEBUG ? THINK_PATH.'Tpl/think_exception.tpl' :$_SERVER['HTTP_HOST'].'/Public/html/404.html',
    //'ERROR_PAGE'   =>   APP_DEBUG ? THINK_PATH.'Tpl/think_exception.tpl' :$_SERVER['HTTP_HOST'].'/Public/html/404.html',

    //PAGE_SIZE
    'PAGE_SIZE' => 15,
);