<?php
if(!defined("ROOT_PATH")){
	define("ROOT_PATH",dirname(str_replace("\\", "/", dirname(__FILE__)))."/");
}
/*
�������ļ�
*/
if(!defined("ROOT_PATH")){
	define("ROOT_PATH","");
}
class cache
{
	public $expire=3600;
	public $cache_dir; 
	public $mem=null;
	public $cache_type="file";
	public $mysql;	
	function __construct()
	{
		$this->init();
		$this->defaultType();
	}
	
	public function init(){
		$this->cache_dir=ROOT_PATH."temp/filecache";
	}
 
	public function keydir($key){
		$d=md5($key);
		return "/".$d{0}."/".$d[1]."/".$d[2]."/";
	}
	public function setType($cache_type){
		$this->cache_type=$cache_type;
		return $this;
	}
	
	 
	
	public function defaultType(){
		global $cacheconfig;
		if($cacheconfig['memcache']){
			$this->cache_type="memcache";
		}elseif($cacheconfig['mysql']){
			$this->cache_type='mysql';
		}else{
			$this->cache_type="file";
		}
		
		
	}
	
	public function set($key,$val,$expire=3600)
	{
		
		 switch($this->cache_type){
			case "memcache":
					$this->mem_set($key,$val,$expire);
				break; 
			case "file":
			
					$this->file_set($key,$val,$expire);
					
				break;
			case "php":
					$this->php_set($key,$val,$expire);
				break;
			case "mysql":
					$this->mysql_set($key,$val,$expire);
				break;
		 }
	}
	
	public function get($key){
		 switch($this->cache_type){
			 case "memcache":
			 			return $this->mem_get($key);
					break;
			 case "file":
						return $this->file_get($key);
					break;
			 case "php":
			 			return $this->php_get($key);
			 		break;
			case "mysql":
					
						return $this->mysql_get($key);	
					break;
		 }
	}
	

	
	/*
	@��ȡ��������
	*@file �ļ���
	*/
	public function file_get($key)
	{
		$key=preg_replace("/[^\w]/","",$key);
		$dir=$this->cache_dir.$this->keydir($key);
		
		$file=$dir.$key.".txt";
		if(file_exists($file)){
			$data=json_decode(file_get_contents($file),true);
			if($data['expire']<time()){
				return false;
			}
			return $data['data'];
			
		}else{
			return false;
		}
		 
	}
	
	/**
	*���û���
	*/
	public function file_set($key,$val,$expire=3600){
		$key=preg_replace("/[^\w]/","",$key);
		$dir=$this->cache_dir.$this->keydir($key);
		$file=$dir.$key.".txt";
		$this->umkdir($dir);
		$data=array("expire"=>time()+$expire,"data"=>$val);
		file_put_contents($file,json_encode($data)); 
	}
	
	/**
	*@��ȡphp���� ����ֱ��include�ļ�
	*/
	public function php_get($key)
	{
		$key=preg_replace("/[^\w]/","",$key);
		$dir=$this->cache_dir.$this->keydir($key);
		$file=$dir.$key.".php";
		if(file_exists($file)){
			@include($file);
			return $$key;
		}else{
			return false;
		}

	}
	 
	
	 
	/**
	*@д��php���� һ���������û��� ������Ч
	*/
	public function php_set($key,$val,$expire=3600)
	{
		$key=preg_replace("/[^\w]/","",$key);
		$dir=$this->cache_dir.$this->keydir($key);
		$file=$dir.$key.".php";
		$content='<?php'." \r\n".'$'.$key.'='.var_export($val,true).";\r\n?>";
		file_put_contents($file,$content);
	}
	
	
	/*
	*mem����
	*/
	
	 
	
	public function mem_set($k,$v,$expire=0){
		if(function_exists("cache_mem_set")){
			cache_mem_set($k,$v,$expire);
		}else{
			$this->file_set($k,$v,$expire);
		}
	}
	
	public function mem_get($k){
		if(function_exists("cache_mem_get")){
			return cache_mem_get($k);
		}else{
			return $this->file_get($k);
		}
	}
	
	/**
	*mysql ����
	*/
	
 
		
	public function mysql_set($k,$v,$expire=3600){
		
		if(function_exists("cache_mysql_set")){
			cache_mysql_set($k,$v,$expire);
		}else{
			$this->file_set($k,$v,$expire);
		}
		
	}
	
	public function mysql_get($k){
		if(function_exists("cache_mysql_get")){
			return cache_mysql_get($k);
		}else{
			return $this->file_get($k);
		}
		 
	}
	
	
	/**
	@ɾ��Ŀ¼����
	*/
	public function clear($type=0)
	{
		$this->delFile($this->cache_dir,$type);
	}
	
	/**
	@ɾ��Ŀ¼�µ������ļ� ������ǰĿ¼
	*/
	function delfile($dir,$rmdir=0)
	{
		$hd=opendir($dir);
		while(($f=readdir($hd))!==false)
		{
			if($f!=".." and $f!=".")
			{
				if(is_file($dir."/".$f)){
					unlink($dir."/".$f);
				}else
				{
					self::delfile($dir."/".$f."/",$rmdir);
				}
			}
		}
		
		closedir($hd);
		if($rmdir)
		{
			rmdir($dir);
		}
	}
	
	
	/*�����ļ���*/
	function umkdir($dir)
	{
		$dir=str_replace(ROOT_PATH,"",$dir);
		$arr=explode("/",$dir);
		foreach($arr as $key=>$val)
		{			
			$d="";
			for($i=0;$i<=$key;$i++)
			{
				$d.=$arr[$i]."/";
			}
			if(!file_exists(ROOT_PATH.$d))
			{ 
				mkdir(ROOT_PATH.$d,0755);
			} 
		}
		
	}

	

		
}

?>