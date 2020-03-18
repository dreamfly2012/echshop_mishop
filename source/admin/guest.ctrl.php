<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class guestControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("guest"));
		}
		
		public function onDefault(){
			$where=" is_temp=0 AND status<8 ";
			$url=APPADMIN."?m=guest&a=default";
			$limit=20;
			$start=get("per_page","i");
			$type_id=get('type_id','i');
			if($type_id){
				$where.=" AND type_id=".$type_id;
			}
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->guest->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"type_list"=>$this->guest->type_list(),
				)
			);
			$this->smarty->display("guest/index.html");
		}
		
 
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->guest->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("guest/show.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->guest->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("guest/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["title"]=post("title","h");
			$data["type_id"]=post("type_id","i");
			$data["userid"]=post("userid","i");
			$data["nickname"]=post("nickname","h");
			$data["email"]=post("email","h");
			$data["telephone"]=post("telephone","h");
			$data["dateline"]=time();
			$data["content"]=post("content","x");
			$data['money']=post('money','r');
			$data['attach']=post('attach','h');
			$data['reply']=post('reply','x');
			if($id){
				$this->guest->update($data,"id='$id'");
			}else{
				$this->guest->insert($data);
			}
			$this->goall("保存成功");
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->guest->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->guest->update(array("status"=>8),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>