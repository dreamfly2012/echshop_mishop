<?php
class sysmsgControl extends skymvc{
	private $status_list;
	private $type_list;
	public function __construct(){
		parent::__construct();
		$this->status_list=array(
			1=>"正常",
			0=>"禁止"
		);
		$this->type_list=array(
			1=>"用户",
			//2=>"商家",
			//3=>"全站"
		);
		$this->loadModel(array("sysmsg","sysmsg_user"));
	}
	
	public function onDefault(){
		$where="";
		$url=APPADMIN."?m=sysmsg";
		$limit=20;
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC",
			"where"=>$where
		);
		$rscount=true;
		$data=$this->sysmsg->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"status_list"=>$this->status_list,
			"type_list"=>$this->type_list,
		));
		$this->smarty->display("sysmsg/index.html");
	}
	
	public function onAdd(){
		$id=get('id','i');
		if($id){
			$data=$this->sysmsg->selectRow(array("where"=>"id=$id"));
		}
		$this->smarty->assign(array(
			"status_list"=>$this->status_list,
			"type_list"=>$this->type_list,
			"data"=>$data,
		));
		$this->smarty->display("sysmsg/add.html");
	}
	
	public function onSave(){
		$id=get_post("id","i");
		$data["title"]=get_post("title","h");
		$data["status"]=get_post("status","i");
		
		$data["content"]=get_post("content","x");
		$data["type_id"]=get_post("type_id","i");
		$data["start_time"]=strtotime(get_post("start_time"));
		$data["end_time"]=strtotime(get_post("end_time")); 
		if($id){
			$this->sysmsg->update($data,"   id=$id");
		}else{
			$data["dateline"]=time();
			$this->sysmsg->insert($data);
		}
		$this->goall("保存成功");
	}
	
	public function onDelete(){
		$id=get_post("id","i");
		$this->sysmsg->delete("  id=$id");
		$this->sysmsg_user->delete(" msgid=$id");
		$this->goall("删除成功");
	}
	
}
?>