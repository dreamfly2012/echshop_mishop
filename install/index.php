<?php
define("ROOT_PATH",  str_replace("\\", "/", dirname(__FILE__))."/");
if(file_exists("../config/install.lock"))
{
	header("Location: ../"); 
	exit;
}
header("Content-type:text/html;charset=utf-8");
require("function.php");
/*加密函数*/
function echoflush($str){
	echo "<div class='logs'>".$str."</div>";
	flush();
	ob_flush();
}
 /*创建文件夹*/
	  function umkdir($dir)
	{
		if(empty($dir)) return false;
		$dir=str_replace(ROOT_PATH,"",$dir);
		$arr=explode("/",$dir);
		
		foreach($arr as $key=>$val)
		{
			$d="";
			for($i=0;$i<=$key;$i++)
			{
				if($arr[$i]!=""){				
					$d.=$arr[$i]."/";
				}
			} 
			if(!file_exists(ROOT_PATH.$d))
			{ 
				mkdir(ROOT_PATH.$d,0755);
			}
		}
	}
function slog($data){
	$f=fopen("log.txt","a+");
	fwrite($f,$data);
	fclose($f);
}
function insert_data($table,$das){
	global $link;
	foreach($das as $data){
		$sql=$sq="INSERT INTO ".$table."  SET ";
		$i=0;
		foreach($data as $k=>$v){
			if($i>0){
				$sql.=",";
			}
			$sql.= " $k = '".$v."' "; 
			$i++;
			 
		}
		// echo $sql;
		mysqli_query($link,$sql);
		 
	}
}

/*检测权限*/
function checkmode($dirs,$type=2){
	$data=array();
	foreach($dirs as $dir){
		if(!file_exists("../".$dir)){
			mkdir("../".$dir);
			chmod("../".$dir,"0777");
		}
		switch($type){
			case 2://写入
				if(!is_writeable("../".$dir)){
					$data[]=$dir." <font style='color:red;'>不可写</font><br>";
				}
				break;
			case 4://读取
				if(!is_readable("../".$dir)){
					$data[]=$dir." <font style='color:red;'>不可读</font><br>";
				}
				break;
			case 1://执行
				if(!is_executable("../".$dir)){
					$data[]=$dir." <font style='color:red;'>不可执行</font><br>";
				}
				break;
			case 6://读写权限
				$s="";
				if(!is_writeable("../".$dir)){
					$s.=" <font style='color:red;'>不可写</font>";
				}
				
				if(!is_readable("../".$dir)){
					$s.=" <font style='color:red;'>不可读</font>";
				}
				if(!empty($s)){
					$data[]=$dir.$s."<br>";
				}
				break;
			case 7://读写执行权限
				$s="";
				if(!is_writeable("../".$dir)){
					$s.=" <font style='color:red;'>不可写</font>";
				}
				
				if(!is_readable("../".$dir)){
					$s.=" <font style='color:red;'>不可读</font>";
				}
				
				if(!is_executable("../".$dir)){
					$s.=" <font style='color:red;'>不可执行</font>";
				}
				if(!empty($s)){
					$data[]=$dir.$s."<br>";
				}
				break;
				
		}
		
	}
	return $data;
}
set_time_limit(0);
require("cls_smarty.php");
$smarty= new Smarty();
$smarty->template_dir="skins";
$smarty->compile_dir="compiled";
if(empty($_REQUEST['step']) || $_REQUEST['step']==1)
{
	//检测目录权限
	$dirs=array(
		"api",
		"attach",
		"config",
		"static",
		"themes",
		"skymvc",
		"skymvc/class",
		"skymvc/function",
		"skymvc/library",
		"source",
		"source/admin",
		"source/index",
		"source/model",
		"temp",
		"temp/caches",
		"temp/compiled",
		"temp/html",
	);
	$data=checkmode($dirs,6);
	if(!empty($data)){
		$smarty->assign("dirs",$data);
	}
	
	$smarty->assign("step",1);
	
	$smarty->display("index.html");
	
}elseif($_REQUEST['step']==2)
{
	$mysql_host=trim($_POST['mysql_host']);
	$mysql_user=trim($_POST['mysql_user']);
	$mysql_pwd=trim($_POST['mysql_pwd']);
	$mysql_db=trim($_POST['mysql_db']);
	$tblpre=trim($_POST['tblpre']);
	 
	$str='<?php
 
define("MYSQL_CHARSET","utf8");
define("TABLE_PRE","'.$tblpre.'");
define("SESSIONDB",0);
$dbconfig["master"]=array(
	"host"=>"'.$mysql_host.'","user"=>"'.$mysql_user.'","pwd"=>"'.$mysql_pwd.'","database"=>"'.$mysql_db.'"
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
define("SESSION_USER",0);
define("TESTMODEL",1);//开发测试模式
define("HOOK_AUTO",true);//开放全局hook
//UPLOAD_OSS--- aliyun/qiniu/upyun/0 不分离上传设为0
define("UPLOAD_OSS",0);
define("UPLOAD_DEL",0);
define("OSS_BUCKET","skycms");
//define("IMAGES_SITE","http://skycms.oss-cn-hangzhou.aliyuncs.com/");
define("IMAGES_SITE","http://".$_SERVER[\'HTTP_HOST\']."/");
//静态文件
define("STATIC_SITE","http://".$_SERVER[\'HTTP_HOST\']."/");
?>';
	file_put_contents("../config/config.php",$str);
	$smarty->assign("step",2);
	 
	$smarty->display("index.html");
	
}elseif($_REQUEST['step']==3)
{
	require_once("../config/config.php");
	//开始创建数据库
	if(!$link=mysqli_connect($dbconfig['master']['host'],$dbconfig['master']['user'],$dbconfig['master']['pwd']))
	{
		echo "<script>alert('服务器连接失败');history.go(-1);</script>";
		exit();
	}
	if(!mysqli_select_db($link,$dbconfig['master']['database']))
	{
		
		mysqli_query($link,"create database ".$dbconfig['master']['database']);
		if(!mysqli_select_db($link,$dbconfig['master']['database']))
		{
			echo "<script>alert('创建数据库失败".$dbconfig['master']['database']."');history.go(-1);</script>";
			exit();
		}
	}
	mysqli_query($link,"SET sql_mode=''");
	mysqli_query($link,"SET NAMES utf8");
	//创建表结构
	$dbfile="install.json";
	if(file_exists($dbfile)){
		$content=file_get_contents($dbfile);
		$iarr=json_decode($content,true);
		if(!empty($iarr)){
			foreach($iarr as $key=>$v){
				echoflush('正在创建表'.$key.'....');
				mysqli_query($link,str_replace("sky_",TABLE_PRE,$v));
			}
		}
	}
	 
	//提取insert
	$dbfile="data.json";
	if(file_exists($dbfile)){
		$content=file_get_contents($dbfile);
		$iarr=json_decode($content,true);
		
		if(!empty($iarr)){
			foreach($iarr as $key=>$v){
				insert_data(str_replace("sky_",TABLE_PRE,$key),$v);
				 
				echoflush('正在向表'.$key.'插入数据');
			}
		}
	}

	$smarty->assign("step",3);
	
	
	 
	echo "<script language=\"JavaScript\">\nfunction moveNew(){\nparent.location.href=\"index.php?m=index&step=4\";\n}\nwindow.setTimeout('moveNew()','2000');\n</script>";
	

}elseif($_REQUEST['step']==4)
{
	
	$smarty->assign("step",4);
	$smarty->display("index.html");
	
}elseif($_REQUEST['step']==5)
{
	if($_POST)
	{	require_once("../config/config.php");
		$link=mysqli_connect($dbconfig['master']['host'],$dbconfig['master']['user'],$dbconfig['master']['pwd']);
		mysqli_select_db($link,$dbconfig['master']['database']);
		mysqli_query($link,"SET NAMES utf8");
		 mysqli_query($link,"SET sql_mode=''");
		$adminname=trim($_POST['adminname']);
		$pwd1=trim($_POST['pwd1']);
		$pwd2=trim($_POST['pwd2']);
		$salt=rand(1000,9999);
		if(empty($adminname))
		{
			echo "<script>alert('管理员不能为空');history.go(-1);</script>";
			exit();
		}
		if(($pwd1!=$pwd2) or empty($pwd1))
		{
			echo "<script>alert('两次输入的密码不一致');history.go(-1);</script>";
		}
		$res=mysqli_query($link,"SELECT * FROM ".TABLE_PRE."admin WHERE username='".$adminname."' ");
		if(mysqli_num_rows($res)){
			mysqli_query($link,"update ".TABLE_PRE."admin set password='".umd5($pwd1.$salt)."',isfounder=1,salt='".$salt."' ");
		}else{
			mysqli_query($link,"insert into ".TABLE_PRE."admin(username,password,isfounder,salt) values('$adminname','".umd5($pwd1.$salt)."',1,'".$salt."')");
		}
		//添加默认站点
		//添加默认用户
		$r2=mysqli_query($link,"select * from ".TABLE_PRE."user where userid=1 ");
		if(!$r2){
			mysqli_query($link,"insert into ".TABLE_PRE."user set userid=1,nickname='admin',username='admin',dateline=".time()." ");
		}
		 
		
	}

	file_put_contents("../config/install.lock","");
	$smarty->assign("step",5);
	$smarty->display("index.html");
}

?>