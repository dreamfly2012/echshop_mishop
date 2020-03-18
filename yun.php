<?php
error_reporting(E_ALL ^ E_NOTICE);
header("Content-type:text/html; charset=utf-8");
if(ini_get("register_globals"))
{
	die("请关闭全局变量");
}
if(!file_exists("config/install.lock"))
{
	header("Location: install/");
	exit;
}

require("config/config.php");
require("config/version.php");
include_once("config/setconfig.php");
require("config/const.php");

define("ROOT_PATH",  str_replace("\\", "/", dirname(__FILE__))."/");
define("CONTROL_DIR","appyun/ctrl");
define("MODEL_DIR","source/model");
define("HOOK_DIR","source/hook");
/*视图模版配置*/
$cache_dir="";//模版缓存文件夹
$compiled_dir="";//模版编译文件夹
$template_dir=$wap_template_dir="appyun/themes/";
$html_dir="";//生成静态文件夹
$rewrite_on=REWRITE_ON;//是否开启伪静态 0不开 1开启
$smarty_caching=true;//是否开启缓存
$smarty_cache_lifetime=3600;//缓存时间
if(isset($_GET['CORS'])){
	if(!defined("ALLOW_ORIGIN")){
		define("ALLOW_ORIGIN","*");
	}
	header("Access-Control-Allow-Origin: ".ALLOW_ORIGIN);
}
if(isset($_GET['siteid'])){
	setcookie("cksiteid",$_GET['siteid'],time()+36000000,"/",DOMAIN);	
	 define("SITEID",intval($_GET['siteid']));
}else{
	 define("SITEID",max(1,$_COOKIE['cksiteid']));
}

if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
	define("INWEIXIN",1);
}else{
	define("INWEIXIN",0);
}
require("./skymvc/skymvc.php");
//用户自定义初始化函数
function userinit(&$base){
	
	  
	
}


?>