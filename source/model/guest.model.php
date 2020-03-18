<?php
class guestModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="guest";
	}
	
	public function type_list(){
		return array(
			//1=>"催单",
			2=>"网站错误",
			3=>"功能建议",
			4=>"投诉",
			//5=>"工程咨询",
			6=>"商家报名"
		);
	}
	
}

?>