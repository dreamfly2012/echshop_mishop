<?php
class orderModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="order";
	}
	
	/**
	"money"=>$money,
					 
			 
					 
		 
					 
	***/
	public function statDayAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		if(!isset($option['k'])){
			$option['k']="order";
		}
		$kday=date("Ymd");
		$where="   k='".$option['k']."' AND kday='".$kday."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		$option['kday']=$kday;
		$row=M("stat_shoporder_day")->selectRow($where);
		if($row){
			
			M("stat_shoporder_day")->update(array(
				"ordernum"=>$row['ordernum']+1,
				"money"=>$row['money']+$option['money']
			),"id=".$row['id']);
		}else{
			
			M("stat_shoporder_day")->insert($option);
		}		
		
	}
	
	public function statWeekAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		if(!isset($option['k'])){
			$option['k']="order";
		}
		$kweek=date("YW");
		$where="   k='".$option['k']."' AND kweek='".$kweek."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		$option['kweek']=$kweek;
		$row=M("stat_shoporder_week")->selectRow($where);
		if($row){
			
			M("stat_shoporder_week")->update(array(
				"ordernum"=>$row['ordernum']+1,
				"money"=>$row['money']+$option['money']
			),"id=".$row['id']);
		}else{
			M("stat_shoporder_week")->insert($option);
		}		
		
	}
	
	public function statMonthAdd($option){
		if(!isset($option['siteid'])){
			$option['siteid']=1;
		}
		if(!isset($option['shopid'])){
			$option['shopid']=1;
		}
		if(!isset($option['k'])){
			$option['k']="order";
		}
		$kmonth=date("Ym");
		$where="   k='".$option['k']."' AND kmonth='".$kmonth."' AND siteid=".$option['siteid']." AND shopid=".$option['shopid']." ";
		$option['kmonth']=$kmonth;
		$row=M("stat_shoporder_month")->selectRow($where);
		if($row){
			
			M("stat_shoporder_month")->update(array(
				"ordernum"=>$row['ordernum']+1,
				"money"=>$row['money']+$option['money']
			),"id=".$row['id']);
		}else{
			M("stat_shoporder_month")->insert($option);
		}		
		
	}
	
}

?>