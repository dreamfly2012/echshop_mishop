<?php
	if(!defined("ROOT_PATH")) exit("die Access ");
	class user_addressControl extends skymvc{
		
		public function __construct(){
			parent::__construct();
			$this->loadmodel(array("user_address","login","district"));
			$this->login->checkLogin();
			$this->userid=$this->login->userid;
		}
		
		public function onDefault(){
			$where=" userid=".$this->userid;
			$url="/index.php?m=user_address&a=default";
			$limit=20;
			$start=get("per_page","i");
			$option=array(
				"start"=>intval(get_post('per_page')),
				"limit"=>$limit,
				"order"=>" id DESC",
				"where"=>$where
			);
			$rscount=true;
			$data=$this->user_address->select($option,$rscount);
			
			if($data){
				foreach($data as $v){
					$d_ids[]=$v['province_id'];
					$d_ids[]=$v['city_id'];
					$d_ids[]=$v['town_id'];
				}
				$dist_list=$this->district->dist_list(array("where"=>" id in(".implode(",",$d_ids).") ","start"=>0,"limit"=>1000000)); 
			}
			$pagelist=$this->pagelist($rscount,$limit,$url);
			$this->smarty->assign(
				array(
					"data"=>$data,
					"pagelist"=>$pagelist,
					"rscount"=>$rscount,
					"url"=>$url,
					"dist_list"=>$dist_list
				)
			);
			$this->smarty->display("user_address/index.html");
		}
		
		public function onShow(){
			$id=get_post("id","i");
			if($plan_id){
				$data=$this->user_address->selectRow("id={$id}");
				
			}
			$this->smarty->assign(array(
				"data"=>$data
			));
			$this->smarty->display("user_address/add.html");
		}
		public function onAdd(){
			$id=get_post("id","i");
			if($id){
				$data=$this->user_address->selectRow("id={$id} AND userid=".$this->login->userid);
				if(empty($data)) $this->goall("数据不存在",1);
				$city_list=$this->district->dist_list(array("where"=>"upid=".$data['province_id']." "));
				$town_list=$this->district->dist_list(array("where"=>"upid=".$data['city_id']." "));
			}
			$province_list=$this->district->dist_list(array("where"=>"upid=".DISTRICTID,"start"=>0,"limit"=>1000000));
			$this->smarty->assign(array(
				"data"=>$data,
				"province_list"=>$province_list,
				"city_list"=>$city_list,
				"town_list"=>$town_list,
			));
			if(get('ajax')){
				$this->smarty->display("user_address/ajax_add.html");
			}else{
				$this->smarty->display("user_address/add.html");
			}
		}
		
		public function onSave(){
			
			$id=get_post("id","i");
			$data["userid"]=$this->userid;
			$data["address"]=get_post("address","h");
			$data["telephone"]=get_post("telephone","h");
			$data["truename"]=get_post("truename","h");
			$data["zip_code"]=get_post("zip_code","h");
			$data["province_id"]=get_post("province_id","i");
			if(empty($data['province_id'])){
				$this->goall("省份不能为空",1);
			}
			$data["city_id"]=get_post("city_id","i");
			$data["town_id"]=get_post("town_id","i");
			$data["dateline"]=time();
			$data['fwshang']=post('fwshang','i');
			if($id){
				$row=$this->user_address->selectRow("id={$id} AND userid=".$this->login->userid);
				if(empty($row)){
					$this->goall("数据出错",1);				
				}
				$this->user_address->update($data,array('id'=>$id));
			}else{
				$id=$this->user_address->insert($data);
			}
			$data['id']=$id;
			if(get('ajax')){
				$data['province']=$this->district->selectOne(array("where"=>"id=".$data['province_id'],"fields"=>"name"));
				$data['city']=$this->district->selectOne(array("where"=>"id=".$data['city_id'],"fields"=>"name"));
				$data['town']=$this->district->selectOne(array("where"=>"id=".$data['town_id'],"fields"=>"name"));
				exit(json_encode(array("error"=>0,"data"=>$data)));
			}else{
				$this->goall($this->lang["save_success"]);
			}
		}
		
		public function onDelete(){
			$id=get_post('id',"i");
			$this->user_address->delete("id={$id} AND userid=".$this->login->userid);
			exit(json_encode(array("error"=>0,"message"=>"删除成功")));
		}
		
		
	}

?>