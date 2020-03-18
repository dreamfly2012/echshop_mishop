<?php
class categoryControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array("category"));
	}
	
	public function onDefault(){
		$this->smarty->display("category/index.html");
	}
	
	public function onAjax_getchild(){
		$pid=get('pid','i');
		
		$data=$this->category->select(array("where"=>array("pid"=>$pid),"order"=>" orderindex asc"));
		
		echo "<option value=0>请选择</option>";
		if($data){
			foreach($data as $k=>$v){
				echo "<option value='{$v['catid']}'>{$v['cname']}</option>";
			}
		}
		exit;
	}
	
}
?>