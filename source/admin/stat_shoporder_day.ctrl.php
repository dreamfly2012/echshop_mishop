<?php
	
	class stat_shoporder_dayControl extends skymvc{
		public $sw; 
		public $k;
		public function __construct(){
			
			parent::__construct();
		 
		}
		
		public function onDefault(){
			$where= " 1 ";
			$start=get('per_page','i');
			$limit=20;
			$url=APPYADMIN."?m=stat_shoporder_day&k=".$this->k;
			
			$startday=get('startday','h');
			$endday=get('endday','h');
			if($startday){
				$where.=" AND kday>=".$startday;
				$url.="&startday=".$startday;
			}
			
			if($endday){
				$where.=" AND kday<=".$endday;
				$url.="&endday=".$endday;
			}
			$sqlcount="select sum(money) as money from ".table('stat_shoporder_day')." where  $where limit 1";
			$where.=" group by kday ";
			
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>10000,
				"fields"=>" kday,sum(money) as money ",				
				"order"=>" id DESC",
			);
			$rscount=true;
			$data=M("stat_shoporder_day")->select($option,$rscount);
			
			
			$total_money=M("stat_shoporder_day")->getOne($sqlcount);
			if(get('a')=='chart'){
				$url.="&a=chart";
				$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$labels .=($k==0)?('"'.substr($v['kday'],4,4).'"'):(',"'.substr($v['kday'],4,4).'"');
						$moneys .=($k==0)?$v['money']:(",".$v['money']);
					}
				}
			}
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(array(
				"list"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"labels"=>$labels,
				"moneys"=>$moneys,
				"total_money"=>$total_money
			));
			if(get('a')=='chart'){
				$this->smarty->display("stat_shoporder_day/chart.html");
			}else{
				$this->smarty->display("stat_shoporder_day/index.html");
			}
		}
		
		public function onAll(){
			
			$where=" 1"; 
			$start=get('per_page','i');
			$limit=20;
			$url=APPADMIN."?m=stat_shoporder_day&k=".$this->k;
			
			$startday=get('startday','h');
			$endday=get('endday','h');
			if($startday){
				$where.=" AND kday>=".$startday;
				$url.="&startday=".$startday;
			}
			
			if($endday){
				$where.=" AND kday<=".$endday;
				$url.="&endday=".$endday;
			}
			switch(get('field')){
				case 'ordernum':
					$field="ordernum";
					break;
				case 'money':
					$field="money";
					break;
				default:
					$field="money";
					break;
			}
			$sqlcount="select sum(".$field.") as $field from ".table('stat_shoporder_day')." where  $where limit 1";
			$where.=" group by kday ";
			
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>10000,
				"fields"=>" kday,sum(".$field.") as $field ",				
				"order"=>" id DESC",
			);
			$rscount=true;
			$data=M("stat_shoporder_day")->select($option,$rscount);
			
			
			$total_money=M("stat_shoporder_day")->getOne($sqlcount);
			 
				$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$labels .=($k==0)?('"'.substr($v['kday'],4,4).'"'):(',"'.substr($v['kday'],4,4).'"');
						$moneys .=($k==0)?$v[$field]:(",".$v[$field]);
					}
				}
			 
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(array(
				"list"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"labels"=>$labels,
				"moneys"=>$moneys,
				"field"=>$field,
				"total_money"=>$total_money
			));
			 
			$this->smarty->display("stat_shoporder_day/all.html");
			 
		}
		
		public function onShop(){
			$where= " 1 ";
			$start=get('per_page','i');
			$limit=20;
			$url=APPADMIN."?m=stat_shoporder_da&a=shop";
			
			$startday=get('startday','h');
			$endday=get('endday','h');
			if($startday){
				$where.=" AND kday>=".$startday;
				$url.="&startday=".$startday;
			}
			
			if($endday){
				$where.=" AND kday<=".$endday;
				$url.="&endday=".$endday;
			}
			
			switch(get('tablename')){
				case 'koudai':
					$tablename="koudai";
					break;
				case "shop":
					$tablename="shop";
					break;
				default:
					$tablename="ymdian";
					break;
					
					
			}
			$where.="  AND k='".$tablename."'";
			
			switch(get('field')){
				case 'ordernum':
					$field="ordernum";
					break;
				case 'money':
					$field="money";
					break;
				default:
					$field="money";
					break;
			}
			$sqlcount="select sum(".$field.") as $field from ".table('stat_shoporder_day')." where  $where limit 1";
			$where.=" group by shopid ";
			
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>10000,
				"fields"=>" shopid,sum(".$field.") as $field ",				
				"order"=>" $field DESC",
			);
			$rscount=true;
			$data=M("stat_shoporder_day")->select($option,$rscount);
			
			
			$total_money=M("stat_shoporder_day")->getOne($sqlcount);
			 
				$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$shopids[]=$v['shopid'];
						$labels .=($k==0)?('"'.substr($v['kday'],4,4).'"'):(',"'.substr($v['kday'],4,4).'"');
						$moneys .=($k==0)?$v[$field]:(",".$v[$field]);
					}
					$shops=M($tablename)->getShopByIds($shopids);
					foreach($data as $k=>$v){
						$v['shop_name']=$shops[$v['shopid']]['title'];
						$data[$k]=$v;
					}
				}
			 
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(array(
				"list"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"labels"=>$labels,
				"moneys"=>$moneys,
				"field"=>$field,
				"total_money"=>$total_money
			));
			 
			$this->smarty->display("stat_shoporder_day/shop.html");
			 
		}
		
		
		
			
	}
	
	
?>