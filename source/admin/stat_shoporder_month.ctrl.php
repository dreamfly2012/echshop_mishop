<?php
	
	class stat_shoporder_monthControl extends skymvc{
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
			$where= $this->sw;
			$start=get('per_page','i');
			$limit=20;
			$url=APPYMDIAN."?m=stat_shoporder_month";
			$where.=" group by kmonth ";
			$option=array(
				"where"=>$where,
				"start"=>$start,
				"limit"=>$limit,
				"fields"=>" kmonth,sum(money) as money ",				
				"order"=>" id DESC",
			);
			$rscount=true;
			$data=M("stat_shoporder_month")->select($option,$rscount);
			if(get('a')=='chart'){
				$url.="&a=chart";
				$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$labels .=($k==0)?('"'.substr($v['kmonth'],4,4).'"'):(',"'.substr($v['kmonth'],4,4).'"');
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
				$this->smarty->display("stat_shoporder_month/chart.html");
			}else{
				$this->smarty->display("stat_shoporder_month/index.html");
			}
		}
		
		 
		
		public function onJieSuan(){
				$id=get('id','i');
				$row=M("stat_shoporder_month")->selectRow("id=".$id);
				if(empty($row)){
					$this->goAll("数据出错",1);
				}
			$money1=$row['money'];
			$money2=$row['money'];	
			$shop=M("ymdian")->selectRow("id=".$row['shopid']);
			M('ymdian')->changenum('money',$money1,"id=".$row['shopid']);
			$nshop=M("ymdian")->selectRow("id=".$row['shopid']);
			$logdata=array(
				"dateline"=>time(),
				"shopid"=>SHOPID,
				"type_id"=>1,
				"ispay"=>1,
				"money"=>$money1,
				"content"=>"您的店铺".$row['kmonth']."月结算，收入".$money1."元,原来".$shop['money'].",现在".$nshop['money']."",
			);
			M('ymdian_pay_log')->insert($logdata);
		}
		
			
	}
	
	
?>