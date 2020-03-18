<?php
class modelControl extends skymvc{
	
	function __construct(){
		parent::__construct();
		$this->loadModel(array('model'));
	}
	
	public function onDefault(){
		$data=$this->model->select(array("where"=>"module=''"));
		$this->smarty->assign(
			array(
				"data"=>$data,
			)
		);
		$this->smarty->display("model/model.html");
	}
	
	public function onAdd(){
		$id=get_post('id','i');
		$this->smarty->assign(
			array(
				"data"=>$this->model->selectRow(array("where"=>array('id'=>$id))),
			)
		);
		$this->smarty->display("model/model_add.html");
	}
	
	public function onSave(){
		$id=get_post('id','i');
		$data['title']=post('title','h');
		
		$data['cat_tpl']=post('cat_tpl','h');
		$data['list_tpl']=post('list_tpl','h');
		$data['show_tpl']=post('show_tpl','h');
		if($id){
			$this->model->update($data,array("id"=>$id));
		}else{
			$data['tablename']=post('tablename','h');
			//检测表名
			if($this->model->selectRow(array("where"=>" tablename='".$data['tablename']."'"))){
				$this->goall("主表已经存在了",1);
			}
			$data['id']=$this->model->insert($data);
			$this->oncopy($data);
			//添加到后台导航
			$this->loadModel("navbar");
			$this->navbar->insert(array(
				"title"=>$data['title'],
				"link_url"=>"?m=".$data['tablename'],
				"pid"=>24,
				"group_id"=>2,
				"m"=>$data['tablename'],
				"a"=>"default",
				
			));
			//添加到配置文件
			$c=file_get_contents("config/model.php");
			file_put_contents("config/model.php",$c."\r\n".'define("MODEL_'.strtoupper($data['tablename']).'_ID",'.$data['id'].');');
		}
		$this->goall($this->lang['model_save_success']);
	}
	
	public  function onStatus(){
		$status=get_post('status','i');
		$id=get_post('id','i');
		$this->model->update(array(
			"status"=>$status
		),"id=".$id);
		$this->goall($this->lang['model_save_success']);
	}
	
	public function onDelete(){
		$id=get_post('id','i');
		if($id<20){
			echo json_encode(array("error"=>1,"message"=>"不可删除"));
		}
		$data=$this->model->selectRow("id=".$id);
		/*卸载数据库*/
		$this->db->query("drop table ".table($data['tablename'])." ");
		$this->db->query("drop table ".table($data['tablename'])."_data ");
		/*End*/
		$this->model->delete(array("id"=>$id));
		echo json_encode(array("error"=>0,"message"=>$this->lang['delete_success']));
	}
	
	public function onCopy($data){
		//自动生成模型文件
		//后台
		umkdir("themes/admin/".$data['tablename']);
		$a_c=file_get_contents("source/admin/article.ctrl.php");
		$a_c=str_replace("article",$data['tablename'],$a_c);
		$a_c=str_replace("ARTICLE",strtoupper($data['tablename']),$a_c);
		$a_c=str_replace("文章",$data['title'],$a_c);
		file_put_contents("source/admin/".$data['tablename'].".ctrl.php",$a_c);
		umkdir("themes/admin/".$data['tablename']);
		$a_c=file_get_contents("themes/admin/article/index.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/admin/".$data['tablename']."/index.html",$a_c);
		$a_c=file_get_contents("themes/admin/article/nav.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/admin/".$data['tablename']."/nav.html",$a_c);
		$a_c=file_get_contents("themes/admin/article/add.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/admin/".$data['tablename']."/add.html",$a_c);
		//前台
		umkdir("themes/index/".$data['tablename']);
		$a_c=file_get_contents("source/index/article.ctrl.php");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("source/index/".$data['tablename'].".ctrl.php",$a_c);
		$a_c=file_get_contents("source/index/list_article.ctrl.php");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("source/index/list_".$data['tablename'].".ctrl.php",$a_c);
		$a_c=file_get_contents("source/index/show_article.ctrl.php");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("source/index/show_".$data['tablename'].".ctrl.php",$a_c);
		
		$a_c=file_get_contents("source/index/articleapi.ctrl.php");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("source/index/".$data['tablename']."api.ctrl.php",$a_c);
		$a_c=file_get_contents("themes/index/article/index.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/index.html",$a_c);
		$a_c=file_get_contents("themes/index/article/list.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/list.html",$a_c);
		$a_c=file_get_contents("themes/index/article/show.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/show.html",$a_c);
		
		$a_c=file_get_contents("themes/index/article/sidebar.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/sidebar.html",$a_c);
		//用户管理
		$a_c=file_get_contents("themes/index/article/add.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/add.html",$a_c);
		
		$a_c=file_get_contents("themes/index/article/my.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/my.html",$a_c);
		
		$a_c=file_get_contents("themes/index/article/home.html");
		$a_c=strtr($a_c,array("article"=>$data['tablename'],"ARTICLE"=>strtoupper($data['tablename']),"文章"=>$data['title']));
		file_put_contents("themes/index/".$data['tablename']."/home.html",$a_c);
		//模型
		$this->db->copy_table("article",$data['tablename']);
		$this->db->copy_table("article_attr",$data['tablename']."_attr");
		$this->db->copy_table("article_data",$data['tablename']."_data");
		$str='<?php
class '.$data['tablename'].'Model extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->table="'.$data['tablename'].'";
	}
}

?>';		
		file_put_contents("source/model/".$data['tablename'].".model.php",$str);
		$str='<?php
class '.$data['tablename'].'_dataModel extends model{
	public $base;
	public function __construct(&$base){
		parent::__construct($base);
		$this->table="'.$data['tablename'].'_data";
	}
}

?>';		
		file_put_contents("source/model/".$data['tablename']."_data.model.php",$str);
	}
	
}
?>