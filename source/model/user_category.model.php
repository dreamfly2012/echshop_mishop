<?php
/**
*Author 雷日锦 362606856@qq.com
*model 自动生成
*/				
class user_categoryModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="user_category";
	}
	
	public function id_title($option){
		if(!isset($option['order'])){
			$option['order']=" orderindex ASC";
		}
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$ndata[$v['id']]=$v['title'];
			}
		}
		return $ndata;
	}
	
	public function children($option=array()){
		if(!isset($option['order'])){
			$option['order']=" orderindex ASC";
		}
		$t1=$this->select($option);
		if($t1){
			foreach($t1 as $k=>$v){
				$t1[$k]['child']=$this->select(array(
					"where"=>" pid=".$v['id'],
					"order"=>" orderindex asc"
				));
			}
		}
		return $t1;
	}
	
	public function id_family($id){
		$ids=$this->selectCols(array("where"=>" pid=".$id));
		if($ids){
			$ids[]=$id;
			return $ids;
		}else{
			return array($id);
		}
	}
}

?>