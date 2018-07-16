<?php
$_GET['m']='API';
$_GET['c']='Ddw';
$_GET['a']='getrate';
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',false);
define('BUILD_DIR_SECURE', false);
define('TMPL_CACHE_ON',false);
define('DB_FIELD_CACHE',false);
define('HTML_CACHE_ON',false);
// 定义应用目录
define('BIND_MODULE', 'Home');
define('APP_PATH' ,'./Application/');
define('SITE_PATH',__DIR__);
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");

// 引入ThinkPHP入口文件
require 'ThinkPHP/ThinkPHP.php';