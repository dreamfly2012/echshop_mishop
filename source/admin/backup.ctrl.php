<?php
class backupControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel("backup");
		$this->backup->database=$GLOBALS['dbconfig']["master"]['database'];
		$this->root_backdir=ROOT_PATH."attach/backup/";
	}
	
	public function onDefault(){
		$backlist=array();
		$dir=$this->root_backdir;
		if(!file_exists($this->root_backdir)){
			umkdir($this->root_backdir,0777);
		}
		$dh=opendir($dir);
		while(false!==($file=readdir($dh)))
		{
			if($file!="." && $file!=".." )
			{
				if(is_dir($dir."/".$file))
				{
				$backlist[]=$file;
				}
			}
		}
		$this->smarty->assign("backlist",$backlist);
		$this->smarty->display("backup.html");
	}
	/*备份表*/
	public function onBacktable(){
		$backdir=$this->root_backdir.date("YmdHis");
		if(!file_exists($backdir))
		{
			umkdir($backdir,0777);
		}
		$_SESSION['ssbackdir']=$backdir;
		$this->backup->backdir=$backdir;
		$this->backup->backTable();
		$this->gourl(APPADMIN."?m=backup&a=backdata");
	}
	/*备份数据*/
	public function onBackData(){
		  $this->backup->backdir=$_SESSION['ssbackdir'];
		  $tables=$this->backup->getTables();
		  $tkey=intval($_GET['tkey']);
		  if($tkey==count($tables)-1)
		  {
			  echo "<a href='javascript:;' onclick='window.close()'>备份完毕</a>";
			  exit();
		  }
		  $table=$tables[$tkey];
		  
		  $rscount=$this->backup->getrscount($table);
		  $limit=10000;
		  $page=max(1,intval($_GET['page']));
		  $start=($page-1)*$limit;
		  echo "正在备份$table.....";
		  if($start<$rscount)
		  {
			  
			  $this->backup->backdata($table,$start,$limit);
			  $page++;
			  echo "<script>location.href='admin.php?m=backup&a=backdata&tkey=$tkey&page=$page';</script>";
			  exit();
			  
		  }
		  $tkey++;
		  echo "<script>location.href='admin.php?m=backup&a=backdata&tkey=$tkey';</script>";
		  exit();
		
	}
	
	/*还原表*/
	public function onRestoreTable(){
		$backdir=get('backdir','h');
		$_SESSION['ssbackdir']=$this->root_backdir.$backdir;
		$this->backup->backdir=$_SESSION['ssbackdir'];
		$this->backup->restoreTable();
		echo "<script>location.href='admin.php?m=backup&a=restoredb';</script>";
		exit();
	}
	
	/*还原数据*/
	public function onRestoreDb(){
		$this->backup->backdir=$_SESSION['ssbackdir'];
		$files=$this->backup->getfiles();
		$fkey=intval($_GET['fkey']);
		if($fkey==count($files)-1)
		{
			echo "<a href='javascript:;' onclick='window.close()'>数据恢复完毕</a>";
			exit();
		}
	
		$file=$files[$fkey];
		$this->backup->restoredb($file);
		$fkey++;
		echo "<script>location.href='admin.php?m=backup&a=restoredb&fkey=$fkey';</script>";
		exit();
	}
	
	
	
}

?>