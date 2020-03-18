<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class gold_logControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("gold_log","user"));
		}
		
		public function onDefault(){
			$where=" isdelete=0 ";
			$url=APPADMIn."?m=gold_log&a=default";
			$limit=20;
			$start=get("per_page","i");
			$userid=get('userid','i');
			if($userid){
				$where.=" AND userid=".$userid;
			}
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->gold_log->select($option,$rscount);
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
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url
				)
			);
			$this->smarty->display("gold_log/index.html");
		}
		
 
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->gold_log->selectRow(array("where"=>"id={$id}"));
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("gold_log/show.html");
		}
		
		public function onDelete(){
			$id=get_post("id","i");
			$this->gold_log->update(array("isdelete"=>1),"id=$id");
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
		public function onMan(){
		
		$this->smarty->display("gold_log/man.html");
	}
	
	public function onsaveman(){
		$userid=post('userid','h');
		$user=$this->user->selectRow("userid='".$userid."' ");
		$money=post('money','r');
		if(empty($user)){
			$this->goall("用户不存在",1);
		}
		$this->loadControl("jfapi");
		$this->jfapiControl->setUserid($user['userid'])->addGold(array(
			"gold"=>$money,
			"content"=>"网站给你人工充值了".$money."个金币，之前￥[oldgold]元，目前有￥[newgold]",
			"type_id"=>3,
			"ispay"=>2,
		));

		$this->goall("金币充值成功");
	}
	}

?>