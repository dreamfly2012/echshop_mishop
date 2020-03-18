<?php
	
	class stat_shoporder_weekControl extends skymvc{
		public $sw;
		public function __construct(){
			
			parent::__construct();
			$k=getStatK();
			$this->sw=" siteid=".SITEID;
			if($k){
				$this->smarty->assign("k",$k);
				$this->sw .=" AND k='".$k."' ";
			}
		}
		
		public function onDefault(){
			$where= $this->sw." AND  shopid=".SHOPID;
			$start=get('per_page','i');
			$limit=20;
			$url=APPYMDIAN."?m=stat_shoporder_day";
			$where.=" group by kweek ";
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>$limit,
				"fields"=>" kweek,sum(money) as money ",				
				"order"=>" id DESC",
			);
			$rscount=true;
			$data=M("stat_shoporder_week")->select($option,$rscount);
			if(get('a')=='chart'){
				$url.="&a=chart";
				$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$labels .=($k==0)?('"'.substr($v['kweek'],4,4).'"'):(',"'.substr($v['kweek'],4,4).'"');
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
				
			));
			if(get('a')=='chart'){
				$this->smarty->display("stat_shoporder_week/chart.html");
			}else{
				$this->smarty->display("stat_shoporder_week/index.html");
			}
		}
		
		 
			
	}
	
	
?>