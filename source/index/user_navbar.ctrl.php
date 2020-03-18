<?php
	/**
	*Author 雷日锦 362606856@qq.com 
	*控制器自动生成
	*/
	if(!defined("ROOT_PATH")) exit("die Access ");
	class user_navbarControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("user_navbar","login"));
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=user_navbar&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" orderindex asc",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->user_navbar->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"type_list"=>$this->user_navbar->type_list()
				)
			);
			$this->smarty->display("user_navbar/index.html");
		}
		
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->user_navbar->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data,
				"type_list"=>$this->user_navbar->type_list()
			));
			$this->smarty->display("user_navbar/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data['url']=post('url','h');
			$data["orderindex"]=post("orderindex","i");
			$data["type_id"]=post("type_id","i");
			$data["dateline"]=time();
			$data["userid"]=$this->login->userid;

			if($id){
				$this->user_navbar->update($data,"id='$id'");
			}else{
				$this->user_navbar->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->user_navbar->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->user_navbar->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>