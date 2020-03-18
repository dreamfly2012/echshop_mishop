<?php
class showControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array('category',"model_index","model"));
	}
	
	public function onDefault(){
		$id=get('id','i');
		$data=$this->model_index->selectRow("id=$id");
		if(empty($data)){
			$data['tablename']="article";
		}
		$ctrl="show_".$data['tablename']."Control";
		$this->loadControl("show_".$data['tablename']);
		if(!is_object($this->$ctrl)){
			$this->gomsg($this->lang['error'],"/index.php");
		}
		
		C("show_".$data['tablename'])->smarty=$this->smarty;
		C("show_".$data['tablename'])->onDefault();
	}
	
	 
}

?>