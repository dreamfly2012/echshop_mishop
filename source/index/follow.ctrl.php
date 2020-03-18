<?php
class followControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadmodel(array("login","user","follow","followed"));
		$this->login->checkLogin();
		$this->userid=$this->login->userid;
	}
	
	public function onDefault(){
		$userid=$this->userid;
		if(get('userid')){
			$userid=get('userid','i');
			$user=$this->user->selectRow("userid=".$userid);
			$this->smarty->assign("user",$user);
		}
		$_GET['type']=$type=max(1,get('type'));
		if($type==2){
			$where =" status=2  AND userid=$userid ";
		}else{
			$where=" userid=$userid ";
		}
		$limit=20;
		$option=array(
			"where"=>$where,
			"limit"=>$limit
		);
		$rscount=true;
		if($type==3){
			$data=$this->followed->select($option,$rscount);
		}else{
			$data=$this->follow->select($option,$rscount);
		}
		
		if($data){
			foreach($data as $k=>$v){
				$uids[]=$v['t_userid'];
			}
			$us=$this->user->getUserByIds($uids);
			foreach($data as $k=>$v){
				$v['t_user_head']=$us[$v['t_userid']]['user_head'];
				$v['t_nickname']=$us[$v['t_userid']]['nickname'];
				
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"pagelist"=>$pagelist
		));
		if($type==3){
			$this->smarty->display("follow/followed.html");
		}else{
			$this->smarty->display("follow/index.html");
		}
		
	}
	
	public function onfollow_btn(){
		$t_userid=get('t_userid','i');
		$userid=$this->login->userid;
		
		if($this->login->userid && $t_userid){
			$this->loadModel("follow");
			$user=$this->user->selectRow("userid=".$t_userid);
			$row=$this->follow->selectRow("userid=".$userid." AND t_userid=".$t_userid."");
			if($row){
				$user['followed']=1;
			}
			$this->smarty->assign("user",$user);
		}
		$data=$this->smarty->fetch("follow/jsbtn.html");
		echo strtojs($data);
		exit;
	}
	
	public function onFollow(){
		$t_userid=get_post('t_userid','i');
		if($this->userid == $t_userid){
			exit(json_encode(array("error"=>1,"message"=>"不能关注自己哦")));
		}
		//判断是否关注了
		$row=$this->follow->selectRow(array("where"=>"t_userid=".$t_userid." AND userid=".$this->userid."   "));
		if(isset($row['status'])  ){
			exit(json_encode(array("error"=>1,"message"=>"你已经关注他了")));
		}else{
			//判断是否被关注 主角我
			$row=$this->followed->selectRow(array("where"=>" t_userid=".$t_userid." AND userid=".$this->userid."  "));
			if(isset($row['status'])){//被关注
				//插入关注表 双向关注
				$data=array(
					"userid"=>$this->userid,
					"t_userid"=>$t_userid,
					"status"=>2,
					"dateline"=>time(),
					
				);
				$data['id']=$this->follow->insert($data);
				 
				
				/******************被关注表************/
				//更新被关注人
				$this->follow->update(array("status"=>2),"  userid=".$t_userid." AND t_userid=".$this->userid."  ");
				// 更新自己
				$this->followed->update(array("status"=>2)," t_userid=".$t_userid." AND userid=".$this->userid."  ");
				
				//插入 被关注的人
				$data=array(
					"t_userid"=>$this->userid,
					"userid"=>$t_userid,
					"status"=>2,
					"dateline"=>time(),
					
				);
				$data['id']=$this->followed->insert($data);
			}else{
				//插入关注表
				$data=array(
					"userid"=>$this->userid,
					"t_userid"=>$t_userid,
					"status"=>1,
					"dateline"=>time(),
					
				);
				$data['id']=$this->follow->insert($data);
				//插入被关注表
				$data=array(
					"t_userid"=>$this->userid,
					"userid"=>$t_userid,
					"status"=>1,
					"dateline"=>time(),
					
				);
				$data['id']=$this->followed->insert($data);
				
			}
			$this->user->changenum("follow_num",1,"userid=".$this->userid);
			$this->user->changenum("followed_num",1,"userid=".$t_userid);					
			exit(json_encode(array("error"=>0,"message"=>"关注成功")));
		}
	}
	
	public function onUnfollow(){
		$t_userid=get_post('t_userid','i');
		$row=$this->follow->selectRow(array("where"=>"t_userid=".$t_userid." AND userid=".$this->userid."  "));
		if(empty($row)){
			exit(json_encode(array("error"=>1,"message"=>"你还未关注他哦")));
		}else{
			//删除关注
			$this->follow->delete("id=".$row['id']);
			if($row['status']==2){//双向关注 更新为关注
				$this->followed->update(array("status"=>1)," t_userid=".$t_userid." AND  userid=".$this->userid."  ");
				$this->followed->delete(" userid=".$t_userid." AND t_userid=".$this->userid."  ");
				
				$this->follow->update(array("status"=>1)," userid=".$t_userid." AND t_userid=".$this->userid."  ");
				
			}else{				
				//删除被关注 单向关注 直接删除
				$this->followed->delete(" userid=".$t_userid." AND t_userid=".$this->userid."  ");
			}
			$this->user->changenum("follow_num",-1,"userid=".$this->userid);
			$this->user->changenum("followed_num",-1,"userid=".$t_userid);				
			exit(json_encode(array("error"=>0,"message"=>"取消关注成功")));
		}
	}
	
}

?>