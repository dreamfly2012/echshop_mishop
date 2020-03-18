<?php
class adModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="ad";
	}
	
	public function adlist($tag_id,$limit=4){
		$data=$this->select(array(
			"where"=>" starttime<".time()." AND endtime>".time()." AND status=2 AND tag_id_2nd=".intval($tag_id)." ",
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit
		));
		if($data){
			foreach($data as $k=>$v){
				$v['info']=$v['info']?$v['info']:$v['title'];
				$v['link2']=$v['link2']?$v['link2']:$v['link1'];
				$data[$k]=$v;
			}
		}
		
		return $data;
	}
	
	public function listbyno($no,$limit=4){
		$tag_id=$this->setTable("ad_tags")->selectOne(array("where"=>"  tagno='".$no."' ","fields"=>"tag_id"));
		$data=$this->select(array(
			"where"=>" starttime<".time()." AND endtime>".time()." AND status=2 AND tag_id_2nd=".intval($tag_id)." ",
			"order"=>" orderindex asc",
			"start"=>0,
			"limit"=>$limit
		));
		if($data){
			foreach($data as $k=>$v){
				$v['info']=$v['info']?$v['info']:$v['title'];
				$v['link2']=$v['link2']?$v['link2']:$v['link1'];
				$data[$k]=$v;
			}
		}
		
		return $data;
	}
}

?>