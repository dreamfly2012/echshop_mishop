<?php
class weiboControl extends skymvc{
	function __construct(){
		parent::__construct();
		$this->loadModel(array("product","weibo_config","user"));
	}
	public function onDefault(){
		$this->smarty->assign("topic",urlencode("网上订餐"));
		$this->smarty->display("weibo.html");
	}
	public function onshare(){//发送微薄
			$rs=$this->product->getRow("SELECT * FROM ".table('product')." WHERE status=2 LIMIT 1 ");
			
			if($rs){
				require_once("config/sina_config.php");
				require_once("api/sina/saetv2.ex.class.php");
				 $nickname=$this->weibo_config->getOne("SELECT nickname FROM ".table('weibo_config')."  LIMIT 1 ");
				$accesstoken=$this->user->getOne("SELECT accesstoken FROM ".table('user')." where nickname='$nickname'  "); 
				if($accesstoken){
					$c = new SaeTClientV2( WB_AKEY , WB_SKEY ,$accesstoken );
					$this->product->query("UPDATE ".table('product')." SET status=3 WHERE id={$rs['id']} ");
					$c->upload("#{$rs['cname']}#".$rs['title']." http://{$_SERVER['HTTP_HOST']}/index.php?m=index&a=show&id={$rs['id']}","http://{$_SERVER['HTTP_HOST']}/".$rs['imgurl']);
				}
			}
			echo "发送成功";
	}
 

	public function onuserlist(){
			$this->assignlist("weibo_souser",20,"   visible=1 "," followers_count desc","/index.php?m=weibo&a=userlist");
			$this->smarty->display("weibo_userlist.html");
	}
	public function onuserindex(){
			
			$uid=get('uid',"i");
			$uid=$uid?$uid:1243015277;
			 $this->smarty->cache_lifetime = 3600*24;
			if(!$this->smarty->is_cached("weibo_userindex.html",$uid)){
				 	
				require_once(ROOT_PATH."config/sina_config.php");
				require_once(ROOT_PATH."api/sina/saetv2.ex.class.php");
				$tk=intval($_SESSION['tk']);
				$token_users=$this->weibo_config->getOne("SELECT token_users FROM ".table('weibo_config')." WHERE 1=1 ");
				$token_users=explode("\r\n",$token_users);
				$tokenarr=$this->user->getCols("SELECT accesstoken FROM ".table('user')." WHERE nickname in("._implode($token_users).") ");
			
				
				$c = new SaeTClientV2( WB_AKEY , WB_SKEY ,$tokenarr[$tk]);
				$data=$c->user_timeline_by_id($uid,1,100,0,0,2);
				
				if(isset($data['statuses'])){
					$screen_name=$data['statuses'][0]['user']['screen_name'] ;
					foreach($data['statuses'] as $k=>$v){
						if(strlen($v['text'])<40) continue;
						$blogs[]=array(
							'picture'=>$v['bmiddle_pic'],
							"content"=>preg_replace("/http:([\w\/\.]+)/i","",$v['text']),
							"dateline"=>strtotime($v['created_at']),
							"screen_name"=>$v['user']['screen_name'],
						);
						
					}
				}elseif($data['error']){
					//切换token
					if($tk>count($tokenarr)-1){
						$_SESSION['tk']=0;
					}else{
						$_SESSION['tk']=$tk+1;
					}
					gourl("/index.php?m=weibo&a=userindex&uid=$uid");
				}
				 
				$seo=array(
					"title"=>"{$screen_name}的幸福女专栏-".$seo['title'],
					"keywords"=>"{$screen_name}的幸福女专栏，".$seo['keywords'],
					"description"=>"{$screen_name}的幸福女专栏,".$seo['description']
				);
				$this->smarty->assign("seo",$seo);
				$this->smarty->assign("screen_name",$screen_name);
				$this->smarty->assign("list",$blogs); 
				
			}
			 $this->smarty->display("weibo_userindex.html",$uid); 
	}
 
	public function ongetbyuid(){
			require_once(ROOT_PATH."config/sina_config.php");
			require_once(ROOT_PATH."api/sina/saetv2.ex.class.php");
			$tk=intval($_SESSION['tk']);
			$token_users=$this->weibo_config->getOne("SELECT token_users FROM ".table('weibo_config')." limit 1 ");
			$token_users=explode("\r\n",$token_users);
			 
			$tokenarr=$this->user->getCols("SELECT accesstoken FROM ".table('user')." WHERE nickname in("._implode($token_users).") ");
			$c = new SaeTClientV2( WB_AKEY , WB_SKEY ,$tokenarr[$tk]);
			$data=$c->show_user_by_id(get('uid','i'));
			if($data['error']){
				//切换token
				if($tk>count($tokenarr)-1){
					$_SESSION['tk']=0;
				}else{
					$_SESSION['tk']=$tk+1;
				}
				exit($a['error']);
			}
			$a=$c->get_tags($data['idstr']);
			$tags="";
			foreach($a as $k=>$v){
				$tags .= array_shift($v) ." ";
			}
			$user=array(	
						'uid'=>$data['idstr'],
						'screen_name'=>$data['screen_name'],
						'location'=>$data['location'],
						'description'=>$data['description'],
						'avatar_large'=>$data['avatar_large'],
						'friends_count'=>$data['friends_count'],
						'domain'=>$data['domain'],
						'followers_count'=>$data['followers_count'],
						'statuses_count'=>$data['statuses_count'],
						"tags"=>$tags						 					
					);
			echo json_encode($user);
	}
	 
}
?>