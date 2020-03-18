<?php
class attribute_catModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="attribute_cat";
	}
	
	public function attr_cat($option=array()){
		$data=$this->select($option);
		if($data){
			$ndata=array();
			foreach($data as $k=>$v){
				$ndata[$v['cat_id']]=$v['title'];
			}
			return $ndata;
		}
	}
	
}

?>