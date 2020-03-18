<?php 
class ratingControl extends skymvc{
	public $shoptpl="index";
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadmodel(array("login","user","shop_rating","rating"));
		$this->userid=$this->login->userid;
	}
	
	
	public function onDefault(){
		$this->onShop();
	}
	
	 
	
	public function onSave(){
		$this->login->checkLogin(1);
		$id=get_post("id","i");
		$data["grade"]=get_post("grade","i");
		$data["userid"]=$this->userid;
		$data["object_id"]=get_post("object_id","i");
	 
		$data["dateline"]=time();
		$data["status"]=get_post("status","i");
		$data["tablename"]=get_post("tablename","h");
		
		$data["content"]=get_post("content","h");
		$data["jf_chuangyi"]=get_post("jf_chuangyi","i");
		$data["jf_zhiliang"]=get_post("jf_zhiliang","i");
		if($this->rating->selectRow(" object_id=".$data['object_id']." AND tablename='".$data['tablename']."' ")){
			exit(json_encode(array("error"=>2,"message"=>"你已经点评过了")));
		}
		if($id){
			$this->rating->update($data,array('id'=>$id));
		}else{
			$this->rating->insert($data);
		}
		exit(json_encode(array("error"=>0,"message"=>$data)));
	}
	
}
?>