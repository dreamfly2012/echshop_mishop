<?php
class loginModel extends model{
	public $userid;
	public $base;
	function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="user";
		$this->userid=$this->getUserId();
	}
	
	public function set($k,$v){
		$_SESSION[$k]=$v;
	}
	
	public function get($k){
		return $_SESSION[$k];
	}
	
	public function getUser($userid=0){
		$userid=$userid?$userid:(isset($_SESSION['ssuser']['userid'])?intval($_SESSION['ssuser']['userid']):0);
		if(!$userid) return false;
		$user=parent::selectRow(array("where"=>"userid=$userid"));
		unset($user['salt']);
		unset($user['password']);
		return $user;
	}
	
	public function getUserId(){
		return (isset($_SESSION['ssuser']['userid'])?intval($_SESSION['ssuser']['userid']):0);
	}
	
	public function checklogin($ajax=0){
		if(get_post('ajax')) $ajax=1;
		if($ajax){
			if(empty($_SESSION['ssuser']['userid'])){
				exit(json_encode(array("error"=>1,"nologin"=>1,"message"=>C()->lang['please_login']) ));
				 
			}
		}else{
			if(empty($_SESSION['ssuser']['userid'])){
				C()->gomsg(C()->lang['please_login'],"/index.php?m=login&a=login");
			}
		}
	}
	
	public function getAdmin($id=0){
		$id=$id?$id:intval($_SESSION['ssadmin']['id']);
		if(!$id) return false;
		return parent::setTable('admin')->selectRow(array("where"=>"id=$id"));
	}
	
	public function checkAdminLogin($ajax=0){
		if(get_post('ajax')) $ajax=1;
		if($ajax){
			if(empty($_SESSION['ssadmin'])){
				exit(json_encode(array("error"=>1,"message"=>C()->lang['please_login'])));
			}
		}else{
			if(empty($_SESSION['ssadmin'])){
				C()->gomsg(C()->lang['please_login'],APPADMIN."?m=admin_login");
			}
		}
	}
	
	public function getShopAdmin($adminid=0){
		$adminid=$adminid?$adminid:intval($_SESSION['ssshopadmin']['adminid']);
		if(!$adminid) return false;
		return parent::setTable('shopadmin')->selectRow(array("where"=>" adminid=$adminid "));
	}
	
	public function getShop($shopid=0){
		if(!$shopid){
			$shopid=intval($_SESSION['ssshopadmin']['shopid']);
		}
		if(!$shopid) return false;
		$data= parent::setTable('shop')->selectRow(array("where"=>"shopid=".$shopid));
		if(empty($data)) return false;
		if($data['userid']==0) $data['userid']=1;
		return $data;
	}
	
	public function checkShopAdmin(){
	
	}
	/*快递员*/
	public function CheckKdyuan(){
		if(empty($_SESSION['sskdyuan'])){
			C()->goall("请先登录",1,"",APPINDEX."?m=kdyuan&a=login");
		}
	}
	
	public function kdyuanLogout(){
		$this->set('sskdyuan',false);
	}
	/*End 快递员*/
	
	public function CodeLogin(){
		if(get_post('authcode')){
			$authcode=get_post('authcode');			
		}else{
			$authcode=$_COOKIE['authcode'];
		}
		if($authcode=='' or $authcode=='null') return false;
		$authcode=jiemi($authcode);
		$arr=explode("|",$authcode);
		$userid=intval($arr[0]);
		$key="login_codelogin_".$userid;
		$user=parent::setTable('user')->selectRow(array("where"=>"userid='".$userid."' "));		
		if($c=cache()->get($key)){
			if($authcode==jiemi($c)){
				$this->userid=$user['userid'];
				$this->set("ssuser",$user);
			}else{
				cache()->set($key,"");
			}
		}else{
			
			if(empty($user) or $arr[1]!=umd5($user['password'])){			
				setcookie("authcode","",time()-3999,"/",DOMAIN);		
			}else{
				$authcode=jiami($user['userid']."|".umd5($user['password']));
				$this->set("ssuser",$user);
				$this->userid=$user['userid'];
				cache()->set($key,$authcode,3600);
				setcookie("authcode",$authcode,time()+3600000,"/",DOMAIN);
				
			}		
		}
	}
	
	public function getaccess(){
		$isvip=$islogin=0;
		if($_SESSION['ssuser']) {
			$islogin=1;
			$vip=parent::setTable("user_vip")->selectRow("userid=".$_SESSION['ssuser']['userid']);
			if($vip && $vip['endtime']>time() ){
				$isvip=1;
			}
			$isvip=1;
		}
		if($_SESSION['ssshopadmin'] or $_SESSION['ssadmin']){
			$isadmin=1;
			$isvip=1;
			$islogin=1;
		}
		
		$isadmin=1;
			$isvip=1;
			$islogin=1;
		$acc=array(
			"isvip"=>$isvip,
			"islogin"=>$islogin,
			"isadmin"=>$isadmin
		);
		return $acc;
	}
	
}
?>