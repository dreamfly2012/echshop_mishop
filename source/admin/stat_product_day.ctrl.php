<?php
	
	class stat_product_dayControl extends skymvc{
		public $sw; 
		public $k;
		public function __construct(){
			
			parent::__construct();
		 
		}
		
		public function onDefault(){
			$where= " 1 ";
			
			$this->smarty->display("stat_product_day/index.html");
			
		}
		
		public function onAll(){
			$where= " 1 ";
			 
			$start=get('per_page','i');
			$limit=20;
			$url=APPADMIN."?m=stat_product_day&k=".$this->k;
			
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
				 
				default:
					$field="ordernum";
					break;
			}
			$sqlcount="select sum(".$field.") as $field from ".table('stat_product_day')." where  $where limit 1";
			$where.=" group by productid ";
			
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>10000,
				"fields"=>" productid,sum(".$field.") as $field ",				
				"order"=>" $field DESC",
			);
			$rscount=true;
			$data=M("stat_product_day")->select($option,$rscount);
			
			
			$total_money=M("stat_product_day")->getOne($sqlcount);
			 
				$labels="";
				$moneys="";
				if($data){
					
					foreach($data as $k=>$v){
						$productids[]=$v['productid'];
					}	
					$products=M("product")->getListByIds($productids);
					 
					foreach($data as $k=>$v){
						$productids[]=$v['productids'];
						$v['title']=$products[$v['productid']]['title'];
						$data[$k]=$v;
					}					
					foreach($data as $k=>$v){
						$labels .=($k==0)?"'".$v['title']."'":(',"'.$v['title'].'"');
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
			$this->smarty->display("stat_product_day/all.html");
		}
		
	}