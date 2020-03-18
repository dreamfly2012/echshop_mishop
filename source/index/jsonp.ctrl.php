<?php
class jsonpControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function onDefault(){
		
	}
	
	public function onModel(){
		$dm=get('dm');
		$this->loadModel("dm");
		$fun=get('fun');
		$option=array(
			"where"=>"",
			"limit"=>10,
		);
		$data=$this->$dm->select($option);
		echo json_encode($data);
	}
	
	public function onControl(){
		
	}

	public function onTest(){
		$this->loadModel("article");
		$data=$this->article->select(array(
			"where"=>"imgurl!='' AND  status<98",
			"limit"=>10,
			"order"=>"id DESC"
		));
		echo get('callback')."(".json_encode($data).")";

	}

	public function onTestShow(){
		$this->loadModel(array("article","article_data"));
		$data=$this->article->selectRow("id=".get('id','i'));
		$data['content']=$this->article_data->selectOne(array("where"=>"id=".get('id','i'),"fields"=>"content"));
		$this->jsonp($data);
	}

	function jsonp($data){
		echo get('callback')."(".json_encode($data).")";
	}
}
?>