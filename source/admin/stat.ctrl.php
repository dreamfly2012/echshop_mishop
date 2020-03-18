<?php
class statControl extends skymvc{
	
	public function __construct(){
		parent::__construct();			
	}
	
	public function onDefault(){
		
	}
	
	public function onNewUser(){
		$where="  1=1 ";
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
		$sqlcount="select sum(userid) as total_user from ".table('user')." where  $where limit 1";
		$where.=" group by kday ";
		$option=array(
			"where"=>$where,
			"fields"=>" count(userid) as total_user,kday "
		);
		 
		$data=M("user")->select($option);
		$total_user=M("user")->getOne($sqlcount);
		$labels="";
				$moneys="";
				if($data){					
					foreach($data as $k=>$v){
						$labels .=($k==0)?('"'.substr($v['kday'],4,4).'"'):(',"'.substr($v['kday'],4,4).'"');
						$moneys .=($k==0)?$v['total_user']:(",".$v['total_user']);
					}
				}	 
		$this->smarty->assign(array(
			"list"=>$data,
			"total_user"=>$total_user,
			"labels"=>$labels,
				"moneys"=>$moneys,
		));
		$this->smarty->display("stat/newuser.html");
		
	}
	
}

?>