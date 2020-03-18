<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class weixin_commandControl extends skymvc{
		public $wx;
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("weixin_command","weixin","login"));
			$this->login->checkLogin();
			$this->wx=$this->weixin->selectRow("userid=".$this->login->userid);
			if(empty($this->wx)) $this->goall("请先申请微信账号",1,0,"/index.php?m=weixin&a=my");
		}
		
		public function onDefault(){
			$where=" wid=".$this->wx['id'];
			$url="/index.php?m=weixin_command&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" isdefault DESC,id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->weixin_command->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
				)
			);
			$this->smarty->display("weixin_command/index.html");
		}
		
 
 
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->weixin_command->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data,
				"fun_list"=>$this->weixin_command->fun_list,
				"type_list"=>$this->weixin_command->type_list,
			));
			$this->smarty->display("weixin_command/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["wid"]=$this->wx['id'];
			$data["title"]=post("title","h");
			$data["command"]=post("command","h");
			$data["type_id"]=post("type_id","i");
			$data["content"]=post("content","h");
			$data["dateline"]=time();
			$data['fun']=post('fun','h');
			$data['isdefault']=post('isdefault','i');
			
			if($data['isdefault']){
				$this->weixin_command->update(array("isdefault"=>0),"wid=".$this->wx['id']);
			}
			if($id){
				$this->weixin_command->update($data,"id='$id'");
			}else{
				$this->weixin_command->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->weixin_command->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->weixin_command->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>