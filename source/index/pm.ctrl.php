<?php
class pmControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array("pm","pm_index","user","login"));
		$this->login->checklogin();
		
	}
	
	public function onDefault(){
			$userid=$this->login->userid;
			$pagesize=20;
			$start=get('per_page','i');
			$where['userid']=$userid;
			$option=array(
				"where"=>$where,
				"order"=>"dateline DESC",
				"start"=>$start,
				"limit"=>$pagesize				
			); 
			$rscount=true;
			$msglist=$this->pm_index->select($option,$rscount);
			if($msglist){
				foreach($msglist as $k=>$v){
					$d=$this->pm->selectRow(array("where"=>"userid=".$v['userid']." AND t_userid=".$v['t_userid']." ","order"=>" id DESC"));
					$v['content']=$d['content'];
					$v['dateline']=$d['dateline'];
					$u=$this->user->selectRow(array("where"=>" userid=".$v['t_userid']));
					$v['t_nickname']=$u['nickname'];
					$v['t_user_head']=$u['user_head'];
					$msglist[$k]=$v;
				}
			}
			$this->smarty->assign("msglist",$msglist);
			
			$this->smarty->assign("pagelist",$this->pagelist($rscount,$pagesize,"index.php?m=pm&status=$status"));
			$this->smarty->assign(array("rscount"=>$rscount));
			
			$this->smarty->display("pm/index.html");
	}
	
	/**
	*改变状态
	*/
	public function onStatus(){
		$id=intval($_GET['id']);
		$this->db->query("UPDATE ".table('pm')." SET status=1 WHERE id='$id' AND touserid='$userid' ");
		$this->gourl();
	}
	
	/**
	*删除全部私信
	*/
	public function onDelete_index(){
		$id=get('id','i');
		$userid=$this->login->userid;
		$rs=$this->pm_index->selectRow(array("where"=>array("id"=>$id)));
	 
		if($rs['userid']!=$userid ) exit(json_encode(array("error"=>1,"message"=>'你无删除权限')));
		//删除索引
		$this->pm_index->delete(array("id"=>$id)); 
		//删除总表
		$this->pm->delete("userid=".$rs['userid']." AND t_userid=".$rs['t_userid']." ");
		exit(json_encode(array("error"=>0,"message"=>"删除成功")));
	}
	/*删除私信*/
	public function onDelete_pm(){
		$id=get('id','i');
		$userid=$this->login->userid;
		$data=$this->pm->selectRow(array("id=$id"));
		if(empty($data)){
			exit(json_encode(array("error"=>1,"message"=>"私信不存在")));
		}
		$rs=$this->pm_index->selectRow(array("where"=>array("userid"=>$userid,"t_userid"=>$data['t_userid'])));
		//删除索引
		if($rs['pm_num']<=1){
			$this->pm_index->delete(array("id"=>$id)); 
		}else{
			$this->pm_index->changenum("pm_num",-1,"id=$id");
		}
		//删除总表
		$this->pm->delete("id=$id ");

		exit(json_encode(array("error"=>0,"message"=>"删除成功")));
	}
	
	public function onSend(){
			$userid=intval($_SESSION['ssuser']['userid']);
			if(get_post('op','h')=='post')
			{
				$nicknames=post('nicknames','h');				
				$content=post('content','h');
				$users=explode("@",$nicknames);
				$users=addslashes_deep($users);			
				$touserids=$this->user->getCols("SELECT userid FROM ".table('user')." WHERE nickname in("._implode($users).") ");				
				if($touserids)
				{
					$dateline=time();
					foreach($touserids as $t_userid )
					{
						//发私信的人
						$f_data['userid']=$userid;
						$f_data['type_id']=2;
						$f_data['t_userid']=$t_userid;
						$f_data['dateline']=$dateline;
						$f_data['status']=1;
						$f_data['content']=$content;
						$newid=$this->pm->insert($f_data);						
						/*私信索引表*/
						if($d=$this->pm_index->selectRow(array("where"=>" userid=$userid AND t_userid={$t_userid}" ))){
							$pi_data=array(
								"id"=>$newid,
								"userid"=>$userid,
								"type_id"=>2,
								"t_userid"=>$t_userid,
								"pm_num"=>$d['pm_num']+1,
							);							 
							$this->pm_index->update($pi_data,array("id"=>$d['id']));
						}else{						
							$this->pm_index->insert(array("id"=>$newid,"userid"=>$userid,"type_id"=>2,"t_userid"=>$t_userid,"pm_num"=>1));
							
						}
						//收私信的人处理
						$t_data['userid']=$t_userid;
						$t_data['type_id']=1;
						$t_data['t_userid']=$userid;
						$t_data['dateline']=$dateline;
						$t_data['status']=1;
						$t_data['content']=$content;	
						$newid=$this->pm->insert($t_data);
						$t_data['id']=$newid;
						/*私信索引表*/						
						if($d=$this->pm_index->selectRow(array("where"=>" userid=".$t_userid." AND  t_userid=".$userid."  ") )){
							$pi_data=array(
								"userid"=>$t_userid,
								"type_id"=>1,
								"t_userid"=>$userid,
								"pm_num"=>$d['pm_num']+1,
							);
							$this->pm_index->update($pi_data,array("id"=>$d['id']));
						}else{						
							$this->pm_index->insert(array("id"=>$newid,"userid"=>$t_userid,"type_id"=>1,"t_userid"=>$userid,"pm_num"=>1));							
						}
					}
				}
				//exit(json_encode(array("error"=>0,"message"=>"发送成功")));
				$this->goall("私信发送成功");	
			}else
			{
				if(get('ajax')){
					$userid=get('userid','i');
					$user=$this->user->selectRow("userid=".$userid);
					if($userid==$this->login->userid){
						echo json_encode(array("error"=>1,"message"=>"不能给自己发私信"));
						exit;
					}
					$this->smarty->assign("user",$user);
					$content=$this->smarty->fetch("pm/pm_send_ajax.html");
					echo json_encode(array("error"=>0,"message"=>$content));
				}else{
					$this->smarty->display("pm/pm_send.html");
				}
			}
		
	}
	
	
	public function onDetail(){
		$t_userid=get('t_userid','i');
		$userid=$this->login->userid;
		$t_user=$this->user->selectRow(array("where"=>array("userid"=>$t_userid),"fields"=>"nickname,userid,user_head"));
		$user=$this->login->getUser();
		//更新状态
		$data['status']=2;//已读
		$this->pm->update($data,"userid=$userid AND  t_userid={$t_userid} ");
		$pagesize=20;
		$start=get('per_page','i');
		$rscount=true;		
		$option=array(
			"where"=>array(
				"userid"=>$userid,
				"t_userid"=>$t_userid,
			),
			"start"=>$start,
			"limit"=>$pagesize,
			"order"=>"id DESC",
		);
		$pmlist=$this->pm->select($option,$rscount);
		if($pmlist){			
			foreach($pmlist as $k=>$v){
				$v['t_nickname']=$t_user['nickname'];
				$v['t_user_head']=$t_user['user_head'];
				$v['nickname']="我";
				$v['user_head']=$user['user_head'];
				$pmlist[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$pagesize,"index.php?m=pm&a=detail&t_userid={$t_userid}");
		$this->smarty->assign(
			array(
				"pmlist"=>$pmlist,
				"t_nickname"=>$t_user['nickname'],
				"pagelist"=>$pagelist,
				"rscount"=>$rscount,
			)
		);
		
		$this->smarty->display("pm/pm_detail.html");
		
	}
	
}

?>