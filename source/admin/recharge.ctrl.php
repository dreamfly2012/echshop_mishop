<?php
class rechargeControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("recharge","user","login"));
	}
	
	public function onDefault(){
		$start=get('per_page','i');
		$limit=20;
		$where=" 1=1 ";
		
		$url=APPADMIN."?m=recharge";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC",
		);
		$rscount=true;
		$data=$this->recharge->select($option,$rscount);
		if($data){
				foreach($data as $v){
					$uids[]=$v['userid'];
				}
				$us=$this->user->getUserByids($uids);
				foreach($data as $k=>$v){
					$v['nickname']=$us[$v['userid']]['nickname'];
					$data[$k]=$v;
				}
			}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("recharge/index.html");
	}
	
	public function onMan(){
		
		$this->smarty->display("recharge/man.html");
	}
	
	public function onsaveman(){
		$userid=post('userid','h');
		$user=$this->user->selectRow("userid='".$userid."' ");
		$money=post('money','r');
		if(empty($user)){
			$this->goall("用户不存在",1);
		}
		$this->loadControl("jfapi");
		$this->jfapiControl->setUserid($user['userid'])->addMoney(array(
			"money"=>$money,
			"content"=>"网站给你人工充值了".$money."元，之前￥[oldmoney]元，目前还剩￥[newmoney]",
			"type_id"=>2,
			"ispay"=>2,
		));
		$this->recharge->insert(array(
			"userid"=>$user['userid'],
			"money"=>$money,
			"pay_type"=>2,
			"pay_orderno"=>"",
			"type_id"=>1,
			"dateline"=>time(),
			"status"=>1,
			
			"orderno"=>$user['userid'].time(),
			"orderinfo"=>post('orderinfo','h')
		));
		$this->goall("充值成功");
	}
}
?>