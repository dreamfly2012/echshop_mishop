<?php
class inviteControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","invite","notice"));
	}
	
	public function onDefault(){
		$url=APPADMIN."?m=invite";
		$rscount=true;
		$start=get('per_page','i');
		$limit=20;
		$where="";
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" id DESC"
		);
		$data=$this->invite->select($option,$rscount);
		if($data){
			foreach($data as $v){
				$uids[]=$v['userid'];
				$uids[]=$v['in_userid'];
			}
			$us=$this->user->getUserByIds($uids);
			foreach($data as $k=>$v){
				$v['user']=$us[$v['userid']];
				$v['inuser']=$us[$v['in_userid']];
				$data[$k]=$v;
			}
			 
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("invite/index.html");
	}
	
	public function onstatus(){
		$id=get('id','i');
		$status=get('status','i');
		$this->invite->update(array("bstatus"=>$status),"id=".$id);
		exit(json_encode(array("error"=>0,"message"=>"发送成功")));
	}
	
	public function onPay(){
		$id=get('id','i');
		$row=$this->invite->selectRow("id=".$id);
		if($row['ispay']) exit(json_encode(array("error"=>1,"message"=>"已发送过")));
		if($row['bstatus']!=1) exit(json_encode(array("error"=>1,"message"=>"必须先审核通过")));
		$this->invite->update(array("ispay"=>1),"id=".$id);
		$this->loadControl("jfapi");
		$money=5;
		$inuser=$this->user->selectRow("userid=".$row['in_userid']);
		$this->jfapiControl->setUserid($row['userid'])->addGold(array(
			"gold"=>$money,
			"content"=>"您成功邀请的用户:".$inuser['nickname']."获得了".$money."个金币，之前￥[oldgold]元，目前有￥[newgold]",
			"type_id"=>4,
			"ispay"=>2,
		));
		//发送通知
		$msg=array(
			"dateline"=>time(),
			"type_id"=>1,
			"status"=>0,
			"userid"=>$row['userid'],
			"content"=>"您成功邀请的用户:".$inuser['nickname']."，获得了".$money."个金币奖励",
		);
		$msg['id']=$this->notice->insert($msg);
		exit(json_encode(array("error"=>0,"message"=>"发送成功")));
	}
	
	
}
?>