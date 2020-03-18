<?php
/**
*Author 雷日锦 362606856@qq.com
*model 自动生成
*/				
class collect_ruleModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="collect_rule";
	}
	
	public function id_title(){
		$d=$this->select(array());
		if($d){
			foreach($d as $v){
				$data[$v['id']]=$v['title'];
			}
			return $data;
		}
	}
}

?>