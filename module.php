<?php
error_reporting(E_ALL ^ E_NOTICE);
if(!file_exists("config/install.lock")){
	header("Location:install");exit;
};
header("Content-type:text/html; charset=utf-8");
if(ini_get('register_globals'))
{
	die('请关闭全局变量');
}
define("ROOT_PATH",  str_replace("\\", "/", dirname(__FILE__))."/");

require(ROOT_PATH."config/rewriterule.php");
require(ROOT_PATH."config/config.php");
require(ROOT_PATH."config/const.php");
require(ROOT_PATH."config/version.php");
require_once(ROOT_PATH."skymvc/function/fun_url.php");//函数库
if(defined("REWRITE_TYPE")  && REWRITE_TYPE=='pathinfo'){
	url_get($_SERVER['REQUEST_URI']);
}
$module=isset($_GET['module'])?$_GET['module']:"";
$m=isset($_GET['m'])?$_GET['m']:"";
$mm=explode("_",$m);
$module=!empty($module)?$module:$mm[0];
$module=str_replace(array("/","\\","."),"",htmlspecialchars($module));
if(empty($m) && empty($module)) exit('模块未安装');


require(ROOT_PATH."module/{$module}/module.php");
define("APPINDEX","module.php");//app入口文件
define("CONTROL_DIR",ROOT_PATH."module/{$module}/source/index");
define("MODEL_DIR",ROOT_PATH."source/model");
define("HOOK_DIR","source/hook");
/*视图模版配置*/
$cache_dir="";//模版缓存文件夹
$template_dir="module/".$module."/themes/index";//模版风格文件夹
$wap_template_dir="module/".$module."/themes/wap";//模版风格文件夹
if(!file_exists($wap_template_dir)){
	$wap_template_dir=$template_dir;	
}

$compiled_dir="";//模版编译文件夹
$html_dir="";//生成静态文件夹
$rewrite_on=REWRITE_ON;//是否开启伪静态 0不开 1开启
$smarty_caching=true;//是否开启缓存
$smarty_cache_lifetime=3600;//缓存时间
 
require("./skymvc/skymvc.php");
//用户自定义初始化函数
function userinit(&$base){
	global $module;
	if(isset($_SESSION['ssuser']['userid'])){
		$base->ssuser=$_SESSION['ssuser'];//当前登录用户的信息
		$base->smarty->assign("ssuser",$base->ssuser);
	}
	$_SESSION['siteid']=SITEID;
	if(get('setsite')){
		$_SESSION['siteid']=get('siteid','i');
	}
	//seo信息
	$base->loadModel("seo");
	$base->seo=$base->seo->get(get('m'),get('a'));
	$base->smarty->assign("seo",$base->seo);
	//seo信息结束
	$base->smarty->assign(array(
		"skins"=>"/themes/index/",
		"skinsmodule"=>"/module/".$module."/themes/index/",
		"skinsdefaultdir"=>"themes/index",
	));
	$base->smarty->assign(
		array(
			"appindex"=>APPINDEX,
			"appmodule"=>APPMODULE,
			"appadmin"=>APPADMIN
			
		));
}

function userinit_model(&$base){
	$base->loadModel(array("login"));
}
 

?>