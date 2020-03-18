<?php
/**
*Author 雷日锦 362606856@qq.com
*model 自动生成
*/				
class user_navbarModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->base=$base;
		$this->table="user_navbar";
	}
	
	public function type_list(){
		return array(
			1=>array(
				"title"=>"文章",
				"url"=>"/index.php?m=article&a=home"
			),
			2=>array(
				"title"=>"图片",
				"url"=>"/index.php?m=picture&a=home"
			),
			3=>array(
				"title"=>"产品",
				"url"=>"/index.php?m=product&a=home"
			),
			4=>array(
				"title"=>"下载",
				"url"=>"/index.php?m=download&a=home"
			),
			5=>array(
				"title"=>"视频",
				"url"=>"/index.php?m=video&a=home"
			),
			6=>array(
				"title"=>"帖子",
				"url"=>"/index.php?m=forum&a=home"
			),
			7=>array(
				"title"=>"商店",
				"url"=>"/index.php?m=shop&a=home"
			),
			8=>array(
				"title"=>"活动",
				"url"=>"/index.php?m=activity&a=home"
			),
			40=>array(
				"title"=>"菜谱",
				"url"=>"/index.php?m=caipu&a=home"
			),
			44=>array(
				"title"=>"租房",
				"url"=>"/index.php?m=zufang&a=home"
			),
			45=>array(
				"title"=>"家居装饰",
				"url"=>"/index.php?m=jiaju&a=home"
			),
			46=>array(
				"title"=>"婚纱摄影",
				"url"=>"/index.php?m=hunsha&a=home"
			),
			47=>array(
				"title"=>"美容美发",
				"url"=>"/index.php?m=meifa&a=home"
			),
			48=>array(
				"title"=>"人才招聘",
				"url"=>"/index.php?m=job&a=home"
			),
			
		);
	}
	
	public function listbyuser($userid=0){
		$userid=$userid?intval($userid):USERID;
		$option=array(
			"where"=>" userid=".$userid." ",
			"order"=>" orderindex ASC",
			
		);
		$data= $this->select($option);

		return $data;
	}
}

?>