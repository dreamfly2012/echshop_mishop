<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class guestControl extends skymvc{
		public $userid;
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("guest","login","user"));
			$this->userid=$this->login->userid;
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=guest&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$type_list=$this->guest->type_list();
			$data=$this->guest->select($option,$rscount);
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"type_list"=>$type_list
				)
			);
			$this->smarty->display("guest/index.html");
		}
		
		public function onTouGou(){
			$this->login->checklogin();
			$id=get('id','i');
			if(!$id){
				if($r=M('guest')->selectRow("is_temp=1 AND userid=".$this->login->userid)){
					$id=$r['id'];
				}else{
					$id=M('guest')->insert(array(
						"userid"=>$this->login->userid,
						"dateline"=>time(),
						"is_temp"=>1
					));
				}
			}
			$data=M('guest')->selectRow("id=".$id);
			if($data['userid']!=$this->login->userid){
				$this->goAll("您无权限修改");
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			
			$this->smarty->display("guest/tougou.html");
		}
		
		public function onGetId(){
			$this->login->checklogin();
			
				if($r=M('guest')->selectRow("is_temp=1 AND userid=".$this->login->userid)){
					$id=$r['id'];
				}else{
					$id=M('guest')->insert(array(
						"userid"=>$this->login->userid,
						"dateline"=>time(),
						"is_temp"=>1
					));
				}
			
			$this->goAll("添加成功",0,array("id"=>$id));
		}
		
		public  function onMyGuestNum(){
			$this->login->checklogin();
			$where=" userid=".$this->userid;
			$option=array(
				"fields"=>"count(*) as ct",
				"where"=>$where
			);
			return $data=$this->guest->selectOne($option);
		}
		public function onMy(){
			$this->login->checklogin();
			$where=" is_temp=0 AND status < 4 AND userid=".$this->userid;
			$url="/index.php?m=guest&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->guest->select($option,$rscount);
			if($data){
				foreach($data as $k=>$v){
					$uids[]=$v['touserid'];
				}
				$us=$this->user->getUserByIds($uids);
				foreach($data as $k=>$v){
					if($v['touserid']){
						$v['tonickname']=$us[$v['touserid']];
					}else{
						$v['tonickname']="管理员";
					}
					$data[$k]=$v;
				}
			}
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("guest/my.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->guest->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("guest/show.html");
		}
		 
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->guest->selectRow(array("where"=>"id={$id}"));
				if($data['userid']!=$this->login->userid){
					$this->goAll("您无权限修改");
				}
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("guest/add.html");
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$checkcode=post('checkcode','j');
			if(isset($_POST['checkcode']) && $checkcode!=$_SESSION['checkcode']){
				$this->goall($this->lang['checkcode_error'],1);
			}
			$data['shopid']=post('shopid','i');
			if(isset($_POST['title'])){
				$data["title"]=post("title","h");
				if(empty($data["title"])){
					$this->goall("主题不能为空！",1);
				}
			}
			$data["type_id"]=post("type_id","i");
			$data["userid"]=$this->login->userid;
			$data["nickname"]=post("nickname","h");
			$data['is_temp']=0;
			if(isset($_POST['email'])){
				if( !is_email(post('email'))){
					$this->goall("请正确输入邮箱",1);
				}
			}
			$data["email"]=post("email","h");
			if(isset($_POST['telephone'])){
				if(preg_match("/^\d{11}$/",post('telephone'))==false){
					$this->goall("请正确输入手机号码",1);
				}
			}
			$data["telephone"]=post("telephone","h");
			$data["dateline"]=time();
			$data['qq']=post('qq','h');
			$data['num']=post('num','i');
			$data['linkurl']=post('linkurl','h');
			if(isset($_POST['content'])){
				$data["content"]=post("content","x");
				if(empty($data["content"])){
					$this->goall("内容不能为空！",1);
				}
			
			}
			if(empty($data['title']) && empty($data['telephone']) && empty($data['content'])){
				$this->goall("请填写完整内容！",1);
			}
			$data['money']=post('money','r');
			$data['attach']=post('attach','h');
			$data['phone2']=post('phone2','h');
			$data['company']=post('company','h');
			$data['address']=post('address','h');
			$data['zipcode']=post('zipcode','h');
			
			if($id){
				$this->guest->update($data,"id='$id'");
			}else{
				$this->guest->insert($data);
			}
			$backurl=$_SERVER['HTTP_REFERER'];
			if($data['type_id']==5){
				//工程咨询
				$html="<div>姓名：{$data['nickname']}</div>";
				$html.="<div>手机：{$data['telephone']}</div>";
				$html.="<div>电话：{$data['phone2']}</div>";
				$html.="<div>邮箱：{$data['email']}</div>";
				$html.="<div>产品说明：{$data['content']}</div>";
				$this->loadModel("email");
				$this->email->sendEmail(SMTPEMAIL,$data['nickname']." ".$data['telephone'],$html);
			}elseif($data['type_id']==6){
				//招商加盟
				$html="<div>公司名称：{$data['company']}</div>";
				$html.="<div>公司地址：{$data['address']}</div>";
				$html.="<div>姓名：{$data['nickname']}</div>";
				$html.="<div>手机：{$data['telephone']}</div>";
				$html.="<div>电话：{$data['phone2']}</div>";
				$html.="<div>邮箱：{$data['email']}</div>";
				$html.="<div>邮编：{$data['zipcode']}</div>";
				$this->loadModel("email");
				$this->email->sendEmail(SMTPEMAIL,$data['nickname']." ".$data['telephone'],$html);
			}
			$this->goall("保存成功",0,"",$backurl);
		}
		
		public function onStatus(){
			$id=get_post('id',"i");
			$status=get_post("status","i");
			$this->guest->update(array("status"=>$status),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"状态修改成功")));
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->guest->delete("id={$id}");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>