<?php
/**
*ajax 数据交互操作
*/
class ajaxControl extends skymvc{
	public $oc_where;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("login"));
		$this->oc_where=$this->login->userid?" (userid=".$this->login->userid." or oc_ssid='".OC_SSID."')  ":"oc_ssid='".OC_SSID."' ";
		$this->userid=$this->login->userid;
	}
	
	public function onDefault(){
		
	}
	
	public function onCart_Num(){
		$data=M("order_cart")->select(array(
			"where"=>$this->oc_where
		));
		$num=0;
		if($data){
			foreach($data as $k=>$v){
				$num +=$v['amount'];
			}
		}
		echo $num;
		
	}
	
	public function onNewMsg(){
		$this->loadModel(array("sysmsg_user","notice"));
		$ct1=$this->notice->getCount("userid=".$this->login->userid." AND status=0 ");
		$ct2=$this->sysmsg_user->getCount("userid=".$this->login->userid." AND status=0 ");
		$this->sexit(json_encode(array(
			"sysmsg"=>intval($ct2),
			"notice"=>intval($ct1),
			"total"=>$ct1+$ct2,
		)));
	}
	
	public function onUserinfo(){
		 
			 
	 
	}
	
	
	/**
	* 区域操作  获取省市地区
	*/
	public function ondistrict(){
		$id=get('id','i');
		echo "<option value=0>请选择</option>";
		if($id){
			$this->loadModel("district");
			$data=$this->district->dist_list(array("where"=>"upid=$id","limit"=>10000));
			if($data){
				foreach($data as $k=>$v){
					echo "<option value=".$k.">".$v."</option>";
				}
			}
		}
		exit; 
	}
}

?>