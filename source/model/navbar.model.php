<?php
class navbarModel extends model{
	public $base=NULL;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="navbar";	
	}
	public function navlist($gid,$pid=0){
		return $this->select(array("where"=>array("group_id"=>$gid,"pid"=>$pid,"status"=>1),"order"=>"orderindex asc"));
	}
	
	public function getTarget(){
		return array(
			"_blank"=>"新窗口",
			"main-frame"=>"右窗口",
			"menu-frame"=>"做窗口",
			"_self"=>"当前窗口",
		);
		
	}
	
	/*导航条分组*/
	public function getGroup(){
		return array(
			1=>C()->lang['navbar_admintop'],
			2=>C()->lang['navbar_adminleft'],
			3=>C()->lang['navbar_top'],
			4=>C()->lang['navbar_middle'],
			5=>C()->lang['navbar_bottom'],
			6=>C()->lang['navbar_member'],
			7=>C()->lang['navbar_member_admin'],
			8=>C()->lang['navbar_weixin']
		);
	}
}
?>