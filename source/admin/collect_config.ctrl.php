<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class collect_configControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("collect_config"));
		}
		
		public function onDefault(){
			$this->onAdd();
		}
		
		public function onAdd(){
			$data=$this->collect_config->selectRow();	
			if(empty($data)){
				$this->collect_config->insert(array(
					"id"=>1
				));
				$data['id']=1;
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("collect_config/add.html");
		}
		
		public function onSave(){
			$data["pi_user"]=post("pi_user","h");
			$data["pi_content"]=post("pi_content","h");
			$data["pwd"]=post("pwd","h");
			$data["online_num"]=post("online_num","i");
			$data["pi_cron"]=post("pi_cron","h");
			$this->collect_config->update($data,"");
			$this->goall("保存成功");
		}
		
		
	}

?>