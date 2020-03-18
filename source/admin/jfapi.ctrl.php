<?php
/**
*积分处理 api系统 包括 金币 积分 金钱
*/
class jfapiControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("user","login"));
		$this->userid=$this->login->userid;
	}
	public function setUserid($userid){
		$this->userid=$userid;
		return $this;
	}
	
	/**
	*金钱操作处理
	*$data=array(
	*	"money"=>1,
	*	"content"=>"您使用xxx购买了xxx，之前￥[oldmoney]，目前还剩￥[newmoney]",
	*	"type_id"=>1,
	*	"ispay"=>1 //1支出 2收入
	*)
	*/
	public function addMoney($data){
		$this->loadModel(array("pay_log"));
		$user=$this->user->selectRow(array("where"=>"userid=".$this->userid));
		$newmoney=$user['money']+$data['money'];
		$money=abs($data['money']);
		$this->user->update(array("money"=>$newmoney),"userid=".$this->userid);
		//写入日志
		$content=str_replace(array("[money]","[oldmoney]","[newmoney]"),array($money,$user['money'],$newmoney),$data['content']);
		$logdata=array(
			"dateline"=>time(),
			"userid"=>$user['userid'],
			"type_id"=>$data['type_id'],
			"ispay"=>$data['ispay'],
			"money"=>$money,
			"content"=>$content
		);
		$logdata['id']=$this->pay_log->insert($logdata);
		return true;
	}
	
	/**
	*金币操作处理
	*$data=array(
	*	"gold"=>1,
	*	"content"=>"您使用金币兑换了xxx,消耗了[gold]个金币，之前[oldgold]个，目前还剩[newgold]",
	*	"type_id"=>1,
	*	"ispay"=>1
	*)
	*/
	public function addGold($data){
		$this->loadModel(array("gold_log"));
		$user=$this->user->selectRow(array("where"=>"userid=".$this->userid));
		$newgold=$user['gold']+$data['gold'];
		$gold=abs($data['gold']);
		$this->user->update(array("gold"=>$newgold),"userid=".$this->userid);
		//写入日志
		$content=str_replace(array("[gold]","[oldgold]","[newgold]"),array($gold,$user['gold'],$newgold),$data['content']);
		$logdata=array(
			"dateline"=>time(),
			"userid"=>$user['userid'],
			"type_id"=>$data['type_id'],
			"ispay"=>$data['ispay'],
			"money"=>$gold,
			"content"=>$content
		);
		$logdata['id']=$this->gold_log->insert($logdata);
		return $newgold;
	}
	/**
	*积分操作处理
	*$data=array(
	*	"grade"=>1,
	*	"content"=>"您使用积分兑换了xxx,消耗了[grade]个积分，之前[oldgrade]个，目前还剩[newgrade]",
	*	"type_id"=>1,
	*
	*)
	*/
	public function addGrade($data){
		$this->loadModel(array("grade_log"));
		$user=$this->user->selectRow(array("where"=>"userid=".$this->userid));
		$newgrade=$user['grade']+$data['grade'];
		$grade=abs($data['grade']);
		$this->user->update(array("grade"=>$newgrade),"userid=".$this->userid);
		//写入日志
		$content=str_replace(array("[grade]","[oldgrade]","[newgrade]"),array($grade,$user['grade'],$newgrade),$data['content']);
		$logdata=array(
			"dateline"=>time(),
			"userid"=>$user['userid'],
			"type_id"=>$data['type_id'],
			"grade"=>$grade,
			"content"=>$content
		);
		$logdata['id']=$this->grade_log->insert($logdata);
		return true;
	}
	
}
?>