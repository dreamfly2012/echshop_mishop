<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class grade_logControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("grade_log"));
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=grade_log&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->grade_log->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("grade_log/index.html");
		}
		
		public function onList(){
			$where="";
			$url="/index.php?m=grade_log&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->grade_log->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("grade_log/index.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->grade_log->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("grade_log/show.html");
		}
		
	}

?>