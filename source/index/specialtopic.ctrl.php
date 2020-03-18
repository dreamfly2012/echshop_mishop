<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class specialtopicControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("specialtopic"));
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=specialtopic&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->specialtopic->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("specialtopic/index.html");
		}
		
		public function onList(){
			$where="";
			$url="/index.php?m=specialtopic&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->specialtopic->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("specialtopic/index.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			$data=$this->specialtopic->selectRow(array("where"=>"id={$id}"));
			if(empty($data)){
				$this->goall($this->lang["data_no_exists"],1,0,"/index.php");				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("specialtopic/".($data['tpl']?$data['tpl']:"show.html"));
		}
		
	}

?>