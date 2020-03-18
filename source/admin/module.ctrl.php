<?php
class moduleControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array("module"));	
	}
	
	public function onDefault(){
		
		$d=ROOT_PATH."module";
		$mods=$this->getmods($d);
		
		$this->smarty->assign(
			array(
				"mods"=>$mods,
			)
		);
		 
		$this->smarty->display("module/module.html");
		
	}
	
	public function onAdd(){
		
		$this->smarty->display("module/add.html");
	}
	
	public function onEdit(){
		$module=get('module','h');
		$data=$this->module->selectRow("module='".$module."'");
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("module/edit.html");
	}
	
	public function onSave(){
		$id=get('id','i');
		$data['title']=post('title','h');
		$data['data']=post('data','h');
		$this->module->update($data,"id=".$id);
		$this->gomsg("保存成功");
	}
	
	
	public function onCreate(){
		$this->loadModel("module");
		$mod_name=post('mod_name','h');
		$mod_dir=post('mod_dir','h');
		$mod_table=post('mod_table','h');
		$mod_info=post('mod_info','h');
		//检测表名
		if($this->module->selectRow(array("where"=>" tablename='".$mod_table."'"))){
			$this->gomsg("主表已经存在了");
		}
		if(file_exists("module/{$mod_dir}")){
			$this->gomsg($this->lang['module_exists']);
		}
		umkdir("module/{$mod_dir}/source/admin");
		umkdir("module/{$mod_dir}/source/shop");
		umkdir("module/{$mod_dir}/source/index");
		umkdir("module/{$mod_dir}/source/model");
		umkdir("module/{$mod_dir}/themes/admin");
		umkdir("module/{$mod_dir}/themes/index");
		umkdir("module/{$mod_dir}/themes/shop");
		 
		//新建住控制文件
		$str='<?php
class '.$mod_dir.'Control extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModuleModel("'.$mod_dir.'",array("'.$mod_table.'"));
	}
	
	public function onDefault(){
		$this->smarty->display("index.html");
	}
}

?>';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/source/index/{$mod_dir}.ctrl.php",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/source/admin/{$mod_dir}.ctrl.php",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/source/shop/{$mod_dir}.ctrl.php",$str);
		//建立主表model
		$str='<?php
class '.$mod_table.'Model extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="'.$mod_table.'";
	}
}

?>';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/source/model/".$mod_table.".model.php",$str);
		//建立模板文件
		$str='{include file="header.html" }
		<h1>开始开发模块吧</h1>
		{include file="footer.html" } 
		';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/index/index.html",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/admin/index.html",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/shop/index.html",$str);
		$str='{include file=\'header.html\'}
<script language="javascript">
function movenew()
{
	document.location=\'{$url}\';
}
setTimeout(movenew,2000);

</script>
<div class="well">
{$message}，如果没有自动跳转请点击 <a href="{$url}">跳转</a>

</div> 

{include file=\'footer.html\' }';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/index/gomsg.html",$str);	
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/admin/gomsg.html",$str);	
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/shop/gomsg.html",$str);	
	 
		
		
		 
		//head
		$str='<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<title>许愿墙</title>
<meta property="qc:admins" content="32662172176121755676375" />
<link href="/plugin/skyweb/skyweb.css" rel="stylesheet">
<script src="/plugin/skyweb/jquery.js"></script>
<script src="/plugin/skyweb/skyweb.js"></script>
<link href="{$skins}images/app.css" rel="stylesheet" />
</head>
';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/admin/head.html",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/index/head.html",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/shop/head.html",$str);
		//header
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/admin/header.html",'');
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/index/header.html",'');
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/shop/header.html",'');
		
		//End header
		//footer
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/admin/footer.html",'');
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/index/footer.html",'');
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/themes/shop/footer.html",'');
		//End footer
		//创建配置文件
		$str='<?php
$config=array(
	"title"=>"'.$mod_name.'",//模块名称
	"module"=>"'.$mod_dir.'",//模块目录
 	"version"=>1.0,//当前版本
	"info"=>"'.$mod_info.'",//模块信息
	"table_pre"=>"'.TABLE_PRE.'",//表前缀
	"adminurl"=>"moduleadmin.php?m='.$mod_dir.'",
	"check_update"=>"http://www.skymvc.com",
);
?>';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/config.php",$str);
		$model_id=$this->module->insert(array(
			"title"=>$mod_name,
			"tablename"=>$mod_dir,
			"data"=>$mod_info,
			"module"=>$mod_dir	
		));
		//创建model_id
		$str='<?php
define("'.strtoupper($mod_dir).'_MODEL_ID",'.$model_id.');
?>';
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/module.php",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/install.sql",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/uninstall.sql",$str);
		app_file_put_contents(ROOT_PATH."module/{$mod_dir}/install.lock","success");
		$this->gomsg($this->lang['module_ctreate_success'],APPADMIN."?m=module");
	}
	
	/*获取所有的模块*/
	public function getmods($dir){
		if(!file_exists($dir)) return false;
		
		$dh=opendir($dir);	
		while(false!==($file=readdir($dh))){
			if($file!="." && $file!=".."){
				if(is_dir($dir."/".$file)){
					if(file_exists($dir."/".$file."/config.php")){
						@include_once($dir."/".$file."/config.php");
					}else{
						continue;
					}
					if(file_exists($dir."/".$file."/install.lock")){
						$config['isinstall']=true;
					}else{
						$config['isinstall']=false;
					}
					$mods[]=$config;
				}
			}
		}
		
		return $mods;
	}
	
	public function onInstall(){
		$this->loadModel("module");
		$module=str_replace("/","",get_post('inmodule','h'));
		$d=ROOT_PATH."module/$module";
		if(file_exists($d."/install.lock")){
			$this->gomsg("该模块已经安装");
		}
		@require_once($d."/config.php");
		
		//加入模型
		$model_id=$this->module->insert(array(
			"title"=>$config['title'],
			"tablename"=>$config["module"],
			"data"=>$config['info'],
			"module"=>$config['module']		
		));
		$str='<?php
define("'.strtoupper($module).'_MODEL_ID",'.$model_id.');
?>';
		app_file_put_contents(ROOT_PATH."module/{$module}/module.php",$str);
		//处理sql
		@define(strtoupper($module)."_MODEL_ID",$model_id);
		$this->dosql($d."/"."install.sql",$config['table_pre']);
		//添加已经安装文件
		file_put_contents($d."/install.lock","");
		$this->gomsg("安装成功",APPADMIN."?m=module&a=default");
	}
	
	public function onUninstall(){
		$module=str_replace("/","",get_post('inmodule','h'));
		$this->loadModel("module");
		//删除文件
		$d=ROOT_PATH."module/$module";
		@require_once($d."/config.php");
		//处理sql
		$this->dosql($d."/"."uninstall.sql",$config['table_pre']);
		@unlink($d."/install.lock");
		$this->module->delete("module='".$module."'");
		$this->gomsg("卸载成功",APPADMIN."?m=module&a=default");
	}
	
	
	public function onDelete(){
		$module=str_replace("/","",get_post('inmodule','h'));
		$this->loadModel("module");
		//删除文件
		$d=ROOT_PATH."module/$module";
		@require_once($d."/config.php");
		//处理sql
		$this->dosql($d."/"."uninstall.sql",$config['table_pre']);
		@unlink($d."/install.lock");
		$this->module->delete("module='".$module."'");
		$this->removedir(ROOT_PATH."module/$module");
		$this->gomsg("删除成功",APPADMIN."?m=module&a=default");
	}
	
	public function dosql($dbfile,$table_pre=""){
		$content=file_get_contents($dbfile);
		//获取创建的数据
		//去掉注释
		$content=preg_replace("/--.*\n/iU","",$content);
		//替换前缀
		if($table_pre){
			$content=str_replace($table_pre,TABLE_PRE,$content);
		}
		$carr=array();
		$iarr=array();
		//提取create
		preg_match_all("/Create table .*\(.*\).*\;[\n]*/iUs",$content,$carr);
		if(!empty($carr)){
			$carr=$carr[0];
			
			foreach($carr as $c)
			{
				$this->db->query($c);
			}
		}
		//提取insert
		preg_match_all("/INSERT INTO .*\(.*\)\;[\n]*/iUs",$content,$iarr);
		if(!empty($iarr)){
			$iarr=$iarr[0];
			//插入数据
			foreach($iarr as $c)
			{
				@$this->db->query($c);
			}
		}
		//提取update
		preg_match_all("/UPDATE .*\(.*\)\;[\n]*/iUs",$content,$iarr);
		if(!empty($iarr)){
			$iarr=$iarr[0];
			//插入数据
			foreach($iarr as $c)
			{
				@$this->db->query($c);
			}
		}
		//提取删除
		preg_match_all("/drop table.*\;/iUs",$content,$iarr);
		$iarr=$iarr[0];
		//插入数据
		if(!empty($iarr)){
			foreach($iarr as $c)
			{
				@$this->db->query($c);
			}
		}
		
	}
	
	public function removedir($dir){
		$dh=opendir($dir);
		while(false !==($file=readdir($dh))){
			if($file!="." && $file!=".." ){
				if(is_dir($dir."/".$file)){
					self::removedir($dir."/".$file);
					@rmdir($dir."/".$file);
				}else{
					@unlink($dir."/".$file);
				}
			}
		}
		@rmdir($dir);
		closedir($dh);
	}
	
	public function movedir($from,$to){
		$dh=opendir($from);
		@mkdir($to);
		while(false !==($file=readdir($dh))){
			if($file!="." && $file!=".." ){
				if(is_dir($from."/".$file)){
					self::movedir($from."/".$file,$to."/".$file);
				}else{
					if(!in_array($file,array("config.php","install.lock","install.sql","uninstall.sql"))){
						copy($from."/$file",$to."/".$file);
					}
				}
			}
		}
		
		closedir($dh);
	}
	
	
	
}

?>