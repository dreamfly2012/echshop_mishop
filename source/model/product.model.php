<?php
class productModel extends model{
	public $base;
	function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="product";
		
	}
	
	public function id_list($option){
		$data=$this->select($option);
		if($data){
			foreach($data as $k=>$v){
				$t[$v['id']]=$v;
			}
			return $t;
		}
		return false;
	}
	
	public function getListByCat($catid,$option=array("where"=>" 1 ")){
		$cids=M("category")->id_family($catid);
		$option['where'] .=" AND catid in("._implode($cids).")";
		$option['limit']=12;
		return $this->select($option);
	}
	
	public function getListByIds($ids){
		if(!empty($ids)){
			$option['where']=" id in("._implode($ids).")";
			$data=parent::select($option);
			 
			if($data){
				$list=array();
				foreach($data as $k=>$v){
					$list[$v['id']]=$v;
				}
				return $list;
			}
		}
		return false;
	}
	
	public function statDayAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		$where=" productid=".$option['productid']." AND k='".$option['k']."' AND kday='".date("Ymd")."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		
		$row=M("stat_product_day")->selectRow($where);
		if($row){
			
			M("stat_product_day")->update(array(
				"ordernum"=>$row['ordernum']+$option['ordernum']
			),"id=".$row['id']);
		}else{
			M("stat_product_day")->insert($option);
		}		
		
	}
	
	public function statWeekAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		$where=" productid=".$option['productid']." AND k='".$option['k']."' AND kweek='".date("YW")."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		
		$row=M("stat_product_week")->selectRow($where);
		if($row){
			
			M("stat_product_week")->update(array(
				"ordernum"=>$row['ordernum']+$option['ordernum']
			),"id=".$row['id']);
		}else{
			M("stat_product_week")->insert($option);
		}		
		
	}
	
	public function statMonthAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		$where=" productid=".$option['productid']." AND k='".$option['k']."' AND kmonth='".date("Ym")."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		
		$row=M("stat_product_month")->selectRow($where);
		if($row){
			
			M("stat_product_month")->update(array(
				"ordernum"=>$row['ordernum']+$option['ordernum']
			),"id=".$row['id']);
		}else{
			M("stat_product_month")->insert($option);
		}		
		
	}
	
}

?>