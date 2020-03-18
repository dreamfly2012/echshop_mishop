<?php
class goldgoodsModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->table="goldgoods";
	}
	
	public function id_list($option){
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$v['imgurl']=images_site($v['imgurl']);
				$t[$v['id']]=$v;
			}
			return $t;
		}
		return false;
	}
}

?>