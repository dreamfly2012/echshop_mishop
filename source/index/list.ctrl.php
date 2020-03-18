<?php
class listControl extends skymvc{
	public $cat_info;//当前分类信息
	function __construct(){
		parent::__construct();
		$this->loadModel(array("category","model"));
	}
	
	function onDefault(){
		$catid=get('catid','i');	
		$category=$this->category->selectRow(array("where"=>"catid=$catid"));
		$mod=$this->model->selectRow("id=".intval($category['model_id']));
		if(empty($mod['module'])){
			$ctrl="list_".$mod['tablename']."Control";
			$this->loadControl("list_".$mod['tablename']);
			if(!is_object($this->$ctrl)){
				$this->gomsg($this->lang['error'],"/index.php");
			}
			 
			C("list_".$mod['tablename'])->smarty=$this->smarty;
			C("list_".$mod['tablename'])->smarty->assign("seo",$category);
			C("list_".$mod['tablename'])->onDefault();
		}else{
			$ctrl="list_".$mod['tablename']."Control";
			$this->loadModuleControl($mod['module'],"list_".$mod['tablename']);
			if(!is_object($this->$ctrl)){
				$this->gomsg($this->lang['error'],"/index.php");
			}
			CC($mod['module'],"list_".$mod['tablename'])->smarty=$this->smarty;
			CC($mod['module'],"list_".$mod['tablename'])->onDefault();
		}
		
	}
	
	
	
}
?>