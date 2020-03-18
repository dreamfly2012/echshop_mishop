<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class checkinControl extends skymvc{
		public $userid;
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("checkin","checkin_index","login","user"));
			$this->userid=$this->login->userid;
		}
		
		public function onDefault(){
			$where="";
			$url="/index.php?m=checkin&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->checkin->select($option,$rscount);
			if($data){
				foreach($data as $k=>$v){
					$u=$this->user->selectRow(array("where"=>"userid=".$v['userid']));
					$v['nickname']=$u['nickname'];
					$v['user_head']=$u['user_head'];
					$data[$k]=$v;
				}
			}
			$pagelist=$this->pagelist($rscount,$limit,$url);
			
			$this->loadConfig("table");
			$mood_list=$this->config_item('checkin_mood_list');
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"mood_list"=>$mood_list,
					"url"=>$url
				)
			);
			$this->smarty->display("checkin/index.html");
		}

		public function onMy(){
			$this->login->checkLogin();
			$where=" userid=".$this->userid;
			$url="/index.php?m=checkin&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->checkin->select($option,$rscount);
			if($data){
				foreach($data as $k=>$v){
					$u=$this->user->selectRow(array("where"=>"userid=".$v['userid']));
					$v['nickname']=$u['nickname'];
					$v['user_head']=$u['user_head'];
					$data[$k]=$v;
				}
			}
			$pagelist=$this->pagelist($rscount,$limit,$url);
			
			$this->loadConfig("table");
			$mood_list=$this->config_item('checkin_mood_list');
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"mood_list"=>$mood_list,
					"url"=>$url
				)
			);
			$this->smarty->display("checkin/my.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($id){
				$data=$this->checkin->selectRow("id={$id}");
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("checkin/show.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($plan_id){
				$data=$this->checkin->selectRow("id={$id}");
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("checkin/add.html");
		}
		
		public function onSave(){
			$this->login->checkLogin();
			$data["userid"]=$this->userid;
			$data["day"]=$day=date("Ymd");
			$data["dateline"]=time();
			$data["mood"]=post("mood",'i');
			$data["content"]=post("content",'h');
			$data["type_id"]=1;
			$data["ip"]=ip();
			if($this->checkin->selectRow(array("where"=>"userid=".$this->userid." AND day=".$day." "))){
				$this->goall("今天你已经签到过了",1);
			}
			$data['id']=$this->checkin->insert($data);
			
			//签到积分 金币 处理
			$this->loadConfig("gold_grade");
			$c_gold=$this->config_item("gold");
			$c_grade=$this->config_item("grade");
			
			$row=$this->checkin_index->selectRow(array("where"=>"userid=".$this->userid." AND type_id=1" ));
			//判断是否间断
			if(!empty($row) && $row['last_day']!=date("Ymd",strtotime("-1 day"))){
				$row['grade']=0;
				$row['days']=0; 
				$row['gold']=0;
			}
			$all_times=isset($row['all_times'])?($row['all_times']+1):1;
			$gold=min($c_gold['checkin']+(isset($row['gold'])?$row['gold']:0),$c_gold['checkin_max']);
			$grade=min($c_grade['checkin']+(isset($row['grade'])?$row['grade']:0),$c_grade['checkin_max']);
			$days=isset($row['days'])?$row['days']+1:1;
			$in_data=array(
				"type_id"=>1,
				"userid"=>$this->userid,
				"dateline"=>time(),
				"grade"=>$grade,
				"gold"=>$gold,
				"last_day"=>$day,
				"last_ip"=>ip(),
				"all_times"=>$all_times,
				"is_valid"=>1,
				"days"=>$days
			);
			if($row){ 
				 $this->checkin_index->update($in_data,"id=".$row['id']);
			}else{
				$this->checkin_index->insert($in_data);
			}
			//处理金币
			$this->loadControl("jfapi");
			$this->jfapiControl->addGold(array(
				"gold"=>$gold,
				"type_id"=>21,
				"ispay"=>2,
				"content"=>"恭喜你连续签到".$days."次，本次获得了".$gold."个金币，之前[oldgold]个，目前[newgold]个",
			));
						 
			//处理积分
			$this->jfapiControl->addGrade(array(
				"grade"=>$grade,
				"type_id"=>1,
				"content"=>"恭喜你连续签到".$days."次，本次获得了".$grade."个积分，之前[oldgrade]个，目前[newgrade]个",
			));	
			$this->goall("签到成功");
		}
		
		
	}

?>