<?php
class product_ksControl extends skymvc{
	
	public function __construct(){
		parent::__construct();	
	}
	
	public function onDefault(){
		
	}
	
	public function onJlist(){
		$object_id=get_post('object_id','i');
		$data=M('product_ks')->select(array("where"=>" object_id=".$object_id));
		exit(json_encode($data));
	}
	
	public function onSave(){
		$data=M('product_ks')->postData();
		if($data['id']){
			$row=M('product_ks')->selectRow("id=".$data['id']);
			M('product_ks')->update($data,"id=".$data['id']);
		}else{
			$data['dateline']=time();
			M('product_ks')->insert($data);
		}
		$this->goAll('保存成功');
	}
	
	public function ondelete(){
		$id=get_post('id','i');
		M('product_ks')->delete("id=".$id);
		$this->goAll('删除成功');
	}
	
}

?>