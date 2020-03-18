<?php
 
define("MYSQL_CHARSET","utf8");
define("TABLE_PRE","sky_");
define("SESSIONDB",0);
$dbconfig["master"]=array(
	"host"=>"localhost","user"=>"root","pwd"=>"123","database"=>"skyshop"
);

 
/**其他分表库
$dbconfig["user"]=array(
	"host"=>"localhost","user"=>"root","pwd"=>"123","database"=>"wei"
);
*/
/*缓存配置*/
$cacheconfig=array(
	"file"=>true,
	"php"=>true,
	"mysql"=>true,
	"memcache"=>false
);
/*用户自定义函数文件*/
$user_extends=array(
	"ex_fun.php"
);
/*Session配置 1为自定义 0为系统默认*/
define("SESSION_USER",1);
define("REWRITE_ON",0); 
define("REWRITE_TYPE","pathinfo");
define("TESTMODEL",1);//开发测试模式
define("HOOK_AUTO",true);//开放全局hook
//UPLOAD_OSS--- aliyun/qiniu/upyun/0 不分离上传设为0
define("UPLOAD_OSS",0);
define("UPLOAD_DEL",0);
define("OSS_BUCKET","skycms");
//define("IMAGES_SITE","http://skycms.oss-cn-hangzhou.aliyuncs.com/");
define("IMAGES_SITE","http://".$_SERVER['HTTP_HOST']."/");
//静态文件
define("STATIC_SITE","http://".$_SERVER['HTTP_HOST']."/");
?>