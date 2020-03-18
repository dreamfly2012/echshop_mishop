<?php
class linkControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array("link"));
		
	}
	
	public function onDefault(){
		$rscount=true;
		
		$option=array(
			"where"=>$where,
		);
		$data=$this->link->select($option,$rscount);
		
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
			)
		);
		$this->smarty->display("link/index.html");
	}
	
	public function onAdd(){
		$id=get_post('id','i');
		if($id){
			$this->smarty->assign("data",$this->link->selectRow(array("where"=>array("id"=>$id))));
		}
		$this->smarty->display("link/add.html");
		
	}
	
	public function onSave(){
		$id=get_post('id','i');
		$data['title']=post('title','h');
		$data['link_url']=post('link_url','h');
		$data['link_img']=post('link_img','h');
		$data['type_id']=post('type_id','i');
		$data['orderindex']=post('orderindex','i');
		$data['is_img']=$data['link_img']?1:0;
		if($id){
			$this->link->update($data,array("id"=>$id));
		}else{
			$this->link->insert($data);
		}
		$this->goall("保存成功");
	}
	
	public function onDelete(){
		$id=get_post('id','i');
		$this->link->delete(array("id"=>$id)); 
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
	}
	
}

?>