<?php
class userModel extends model{
	public $base;
	function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="user";
	}
	
	public function is_auth_list(){
		return array(
			1=>"已认证",
			2=>"未通过认证",
			3=>"等待认证",
		);
	}
	
	public function getUserByIds($uids,$fields=""){
		if(!empty($uids)){
			$option['where']=" userid in(".implode(",",$uids).")";
			$fields && $option['fields']=$fields;
			$data=parent::select($option);
			if($data){
				$userlist=array();
				foreach($data as $k=>$v){
					unset($v['password']);
					unset($v['salt']);
					$userlist[$v['userid']]=$v;
				}
				return $userlist;
			}
		}
		return false;
	}
	
	/**
		收入支出处理
		$option=array(
			"userid"=>1,
			"money"=>$money,
			"content"=>"您获得了[money]元",
			"siteid"=>SITEID
		) 	
	**/
	
	public function addMoney($option){
		$userid=$option['userid'];
		$row=$this->selectRow("userid=".$userid);
		if(empty($row)) return false;
		$data=array();
		if(isset($option['money'])){
			$data['money']=$row['money']+$option['money'];
			
		}
 
		 
		$this->update($data,"userid=".$row['userid']);
		 
		$logdata=array(
				"dateline"=>time(),
				"userid"=>$userid,
				"type_id"=>1,
		 
				"ispay"=>$option['money']>0?2:1,				 
		);
		
		
		 
		
		if(isset($option['money'])){
			 
			$logdata['money']=$option['money'];
			$logdata['content']=str_replace("[money]",$option['money'],$option['content'])." 原来".$row['money']."元，现在".$data['money']."元";
			M("pay_log")->insert($logdata);
		}
		
	}
	
	
	
}
?>