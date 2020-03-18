<?php
class attributeControl extends skymvc{
	 
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","attribute_cat","attribute"));
	}
	
	public function onDefault(){
		$start=get('per_page','i');
		$url=APPADMIN."?m=attribute";
		$cat_id=get('cat_id','i');
		if($cat_id){
			$where=" cat_id=".$cat_id." ";
			$url.="&cat_id=".$cat_id;
		}
		$option=array(
			"start"=>$start,
			"limit"=>20,
			"where"=>$where,
			"order"=>" orderindex ASC"
		);
		$rscount=true;
		$data=$this->attribute->select($option,$rscount);
		$pagelist=$this->pagelist($rscount,20,$url);
		$this->smarty->assign(array(
			"attr_cat"=>$this->attribute_cat->attr_cat(),
			"data"=>$data,
			"rscount"=>$rscount,
			"attr_type_list"=>$this->attribute->attr_type(),
			"pagelist"=>$pagelist,
			"input_type_list"=>$this->attribute->input_type(),
		));
		$this->smarty->display("attr/index.html");
	}
	
	public function onAdd(){
		$id=get('id','i');
		if($id){
			$data=$this->attribute->selectRow(array("where"=>"id=$id"));
		}
		$this->smarty->assign(array(
			"attr_cat"=>$this->attribute_cat->attr_cat(),
			"data"=>$data,
			"attr_type_list"=>$this->attribute->attr_type(),
			"input_type_list"=>$this->attribute->input_type(),
		));
		$this->smarty->display("attr/add.html");
	}
	
	public function onSave(){
		$id=get_post("id","i");
		$data["title"]=get_post("title","h");
		$data["cat_id"]=get_post("cat_id","i");
		$data["type_id"]=get_post("type_id","i");
		$data["attr_type"]=get_post("attr_type","i");
		$data["col_name"]=get_post("col_name","h");
		$data["orderindex"]=get_post("orderindex","i");
		$data["content"]=get_post("content","h");
		$data["input_type"]=get_post("input_type","i");
		$data['ishtml']=post('ishtml','i');
		if($id){
			$this->attribute->update($data,array('id'=>$id));
		}else{
			$this->attribute->insert($data);
		}
		$this->goall($this->lang["save_success"]);
		
	}
	
	public function onOrder(){
		$os=$_POST['orderindex'];
		foreach($os as $k=>$v){
			$this->attribute->update(array("orderindex"=>intval($v)),"id=".intval($k));
		}
		$this->goall("排序修改成功");
	}
	
	public function  onDelete(){
		$id=get('id','i');
		$this->attribute->delete("id=$id");
		exit(json_encode(array("error"=>0,"message"=>"删除成功")));
	}
	
}
?>