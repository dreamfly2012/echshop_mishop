<?php
class getwebControl extends skymvc{
	public $sqldata=array(),$insertdata=array();
	public $dir;
	public function __construct(){
		parent::__construct();
		session_write_close();
		set_time_limit(0);
	}
	
	public function onDefault(){
		$data=M("getweb")->select();
		$this->smarty->assign(array(
			"data"=>$data
		));
		$this->smarty->display("getweb/index.html");
	}
	
	public function onAdd(){
		$id=get('id','i');
		$mod_list=$this->module();
		if($id){
			$data=M("getweb")->selectRow("id=".$id); 
			$mod=json_decode(base64_decode($data['content']),true);
			 
			if(!empty($mod)){
			foreach($mod_list as $k=>$v){
				$v['selected']=0;
				if(in_array($k,$mod)){
					$v['selected']=1;
				}
				$mod_list[$k]=$v;
			}
			}
		}
		
		$this->smarty->assign(array(
			"mod_list"=>$mod_list,
			"data"=>$data
		));
		$this->smarty->display("getweb/add.html");
	}
	
	
	public function onSave(){
		$data=M("getweb")->postData();
		$data['content']=base64_encode(json_encode(post('mod')));
		if($data['id']){
			M("getweb")->update($data,"id=".$data['id']);
		}else{
			M("getweb")->insert($data);
		}
		$this->goAll("保存成功",0);
	}
	
	public function onDelete(){
		$id=get('id','i');
		M("getweb")->delete("id=".$id);
		$this->goAll("删除成功",1);
	}
	
	public function onCreate(){
		$id=get('id');
		$row=M("getweb")->selectRow("id=".$id); 
		$sitename=$row['title']; 
		$this->dir=$dir="diyapp/".$sitename."/";
		umkdir($dir);
		$mod=json_decode(base64_decode($row['content']),true);
		if($mod){
			foreach($mod as $k=>$v){
				$this->copymodule($k);
			}
		}
		$this->goall("success");
	}
	
	public function copymodule($model){
		$data=$this->module($model);
		if($data){
			//文件
			if(!empty($data['file'])){
				$f=implode(",",$data['file']);
				$files=explode(",",$f);
				foreach($files as $v){
					$this->copyfile($v);
				}
			}
			//文件夹
			if(!empty($data['dir'])){
				$d=implode(",",$data['dir']);
				$dirs=explode(",",$d);
				foreach($dirs as $v){
					$this->copydir($v);
				}
			}
			//数据库
			if(!empty($data['table'])){
				$d=implode(",",$data['table']);
				 
				$ms=explode(",",$d);
				if($ms){
					foreach($ms as $m){
						$this->copyModel($m);
						$this->sqldata[$m]=$this->getSql($m);
						$this->copyfilebytable($m);
					}
				}
			}
			//插入数据
			if(!empty($data['tabledata'])){
				$d=implode(",",$data['tabledata']);
				$ms=explode(",",$d);
				if($ms){
					foreach($ms as $m){
						$all=$this->getSqlInsert($m);
						if($all){
							foreach($all as $v){
								$this->insertdata[TABLE_PRE.$m][]=$this->mysql_escape($v);
							}
						}
						
					}
				}
			}
			
			umkdir($this->dir."install/");
			file_put_contents($this->dir."install/install.json",json_encode($this->sqldata));
			file_put_contents($this->dir."install/data.json",json_encode($this->insertdata));
		}
	}
	
	function mysql_escape($arr){
		foreach($arr as $k=>$v){
			$arr[$k]=addslashes($v);
		}
		return $arr;
	}
	
	public function onSuccess(){
		
	}
	/*复制Model*/
	function copyModel($model){
		$model=trim($model);
		$file="source/model/".$model.".model.php";
		if(!file_exists($file)) return false;
		$newfile=$this->dir.$file;
		umkdir(dirname($newfile));
		copy($file,$newfile);
	}
	
	function copyfilebytable($m){
		$this->copyfile("source/admin/".$m.".ctrl.php");
		$this->copyfile("source/index/list_".$m.".ctrl.php");
		$this->copyfile("source/index/show_".$m.".ctrl.php");
		$this->copyfile("source/index/".$m.".ctrl.php");
		$this->copyfile("source/index/".$m."api.ctrl.php");
		$this->copydir("themes/admin/".$m);
		$this->copydir("themes/index/".$m);
		$this->copydir("themes/wap/".$m);
	}
	/*转移文件*/
	function copyfile($file){
		$file=trim($file);
		if(!file_exists($file)) return false;
		$newfile=$this->dir.$file;
		umkdir(dirname($newfile));
		copy($file,$newfile);
	}
	
	/*复制文件夹*/
	function copydir($dir){
		$dir=trim($dir);
		if(!file_exists($dir)) return false;
		if(!is_dir($dir)) return false;
		$dh=opendir($dir);
		while(($file=readdir($dh))!==false)
		{
			if($file!="." && $file!="..")
			{
				if($file=='_notes') continue;
				if(is_dir($dir."/".$file))
				{
					
					$this->copydir($dir."/".$file);	
				}else
				{
					$newfile= $dir."/".$file;
					$this->copyfile($newfile);
					
				}
			}
		}
		closedir($dh);
	}
	
	public function getSql($table){
		$table=trim($table);
		$r=$this->db->getRow("show tables like '%".TABLE_PRE.$table."%' ");
		if($r){
			$d=$this->db->getRow("show create table  ".TABLE_PRE.$table);
			return $d['Create Table'];
		}
	}
	
	public function getSqlInsert($table){
		$table=trim($table);
		$r=$this->db->getall("show tables like '%".TABLE_PRE.$table."%' ");
		if($r){
			$d=$this->db->getAll("select * from  ".TABLE_PRE.$table." LIMIT 1000000" );
			 
			return $d;
		}
	}
	
	public function module($k=NULL){
		$dir=ROOT_PATH."getweb";
		$dh=opendir($dir);
		
		while(($file=readdir($dh))!==false){
			if($file!="." && $file!="..")
			{				
				include $dir."/".$file;
			}
		}
		if($k){
			return $data[$k];
		}else{
			return $data;
		}
		
	}
		
}
?>