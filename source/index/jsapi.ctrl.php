<?php
class jsapiControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login","user"));
	}
	
	public function  love_btn($id,$tablename,$userid=0){
		if(!$this->login->userid) return false;
		$id=$id?$id:get('id','i');
		$userid=$userid?$userid:get('userid','i');
		$this->loadModel(array("love"));
		$islove=$this->love->selectRow(" object_id=".$id." AND tablename='".$tablename."'  ");
		$this->smarty->assign(array(
			"islove"=>$islove,
			"addurl"=>"/index.php?m=love&a=add&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
			"removeurl"=>"/index.php?m=love&a=delete&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
		));
		$data=$this->smarty->fetch("love/jsbtn.html");
		echo $data;
	}
	
	public function  onfav_btn(){
		
		$id=get('id','i');
		$tablename=get('tablename','h');
		$t_userid=get('t_userid','i');
		$this->loadModel(array("fav"));
		if(!$this->login->userid){
			$isfav=0;
		}else{
			$isfav=$this->fav->selectRow(" object_id=".$id." AND tablename='".$tablename."'   ");
		}
		$this->smarty->assign(array(
			"isfav"=>$isfav,
			"addurl"=>"/index.php?m=fav&a=add&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
			"removeurl"=>"/index.php?m=fav&a=delete&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."",
		));
		$data=$this->smarty->fetch("fav/jsbtn.html");
		echo strtojs($data);
		exit;
	}
	
	public function onLove_btn(){
		$id=get('id','i');
		$tablename=get('tablename','h');
		$t_userid=get('t_userid','i');
		$this->loadModel(array("love"));
		if(!$this->login->userid){
			 $islove=0;
		}else{
			
			$islove=$this->love->selectRow(" object_id=".$id." AND tablename='".$tablename."'  ");
			
		}
		$this->smarty->assign(array(
				"islove"=>$islove,
				"addurl"=>"/index.php?m=love&a=add&ajax=1&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."&type_id=1",
				"removeurl"=>"/index.php?m=love&a=delete&ajax=1&object_id=".$id."&t_userid=".$t_userid."&tablename=".$tablename."&type_id=1",
			));
		$data=$this->smarty->fetch("love/jsbtn.html");
		echo strtojs($data);
		exit;
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
	
}
?>