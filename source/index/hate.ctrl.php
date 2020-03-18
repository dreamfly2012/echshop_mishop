<?php
class hateControl extends skymvc{
	public $userInfo;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("hate","hate_all","login"));
		$this->login->checklogin();
		$this->userInfo=$this->login->getUser();
	}
	/*添加讨厌组件*/
	public function onAdd(){
		
	}
	/*检测讨厌组件*/
	public function onCheck(){
		
	}
	
	/*删除讨厌组件*/
	public function onDelete(){
		
	}
}
?>