<?php
class articleControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","model","article","article_data","login","attribute_cat","attribute","article_attr","model_index","user","user_category"));
		if(in_array(get('a'),array("my","save","add","mylove"))){
			$this->login->checklogin();
		}
		$this->userid=$this->login->userid;
		$this->smarty->assign("nav","article");
	}
	
	function onDefault(){
		$rscount=true;
		$where=" status=2  ";
		$url="/index.php?m=article";
		
		$start=get_post('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"last_time DESC"
		);
		$data=$this->article->select($option,$rscount);
		if($data){
			$t_ids=array();
			foreach($data as $k=>$v){
				$t_ids[]=$v['catid'];
				$t_uids[]=$v['userid'];
			}
			if($t_ids){
				$t_c=$this->category->cat_list(" catid in("._implode($t_ids).")");
			}
			$this->loadModel("user");
			if($t_uids){
				$t_u=$this->user->getUserByIds($t_uids);
			}
			foreach($data as $k=>$v){
				$v['cname']=$t_c[$v['catid']];
				if(isset($t_u[$v['userid']])){
					$v['nickname']=$t_u[$v['userid']]['nickname'];
				}
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"list"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("article/index.html");
	}
	
	
	public function onHome(){
		$userid=get('userid','i');
		if(empty($userid)){
			$userid=$this->login->userid;
		}
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall($this->lang['user_no_exists'],1,0,"/index.php");
		$limit=20;
		$where=" status=2 AND userid=".$userid."   ";
		$url="/index.php?m=article&a=home&userid".$userid;
		$user_catid=get('user_catid','i');
		if($user_catid){
			$ucids=$this->user_category->id_family($user_catid);
			$where.=" AND user_catid in("._implode($ucids).") ";
			$url.="&user_catid=".$user_catid;
		}
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
		$data=$this->article->select($option,$rscount);
		if($data){
			$t_ids=array();
			foreach($data as $k=>$v){
				$t_ids[]=$v['catid'];
				$t_uids[]=$v['userid'];
			}
			if($t_ids){
				$t_c=$this->category->cat_list(" catid in("._implode($t_ids).")");
			}
			$this->loadModel("user");
			if($t_uids){
				$t_u=$this->user->getUserByIds($t_uids);
			}
			foreach($data as $k=>$v){
				$v['cname']=$t_c[$v['catid']];
				if(isset($t_u[$v['userid']])){
					$v['nickname']=$t_u[$v['userid']]['nickname'];
				}
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"user"=>$user,
				"user_cat_list"=>$this->user_category->id_title(array("where"=>"userid=".$userid)),
				
			)
		);
		$this->smarty->display("article/home.html");
	}
	
	/*我的文章模型*/
	public function onMy(){
		$this->login->checklogin();
		$limit=20;
		$where=" status<99 AND userid=".$this->login->userid;;
		$url="/index.php?m=article&a=my";
		$user_catid=get('user_catid','i');
		if($user_catid){
			$ucids=$this->user_category->id_family($user_catid);
			$where.=" AND user_catid in("._implode($ucids).") ";
			$url.="&user_catid=".$user_catid;
		}
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
		$data=$this->article->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$data[$k]['cname']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid']),"fields"=>"cname"));
				if($v['catid_2nd']){
					$data[$k]['cname_2nd']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid_2nd']),"fields"=>"cname"));
				}
				
				if($v['catid_3nd']){
					$data[$k]['cname_3nd']=$this->category->selectOne(array("where"=>array("catid"=>$v['catid_3nd']),"fields"=>"cname"));
				}
				
				
			}
		}
		
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
				"user_cat_list"=>$this->user_category->id_title(array("where"=>"userid=".$this->login->userid)),
				"pid_list"=>$this->user_category->children(array("where"=>" tablename='article' AND pid=0 AND userid=".$this->login->userid))
			)
		);
		$this->smarty->display("article/my.html");
	}
	
	public function onMyLove(){
		$this->loadModel(array("love"));
		$userid=$this->login->userid;
		$limit=20;
		$start=get_post("per_page","i");
		$option=array(
			"limit"=>$limit,
			"start"=>$start,
			"order"=>" id DESC",
			"where"=>" userid=".$userid." AND tablename='article' ",
		);
		$rscount=true;
		$data=$this->love->select($option,$rscount);
		 
		if($data){
			foreach($data as $k=>$v){
				$a=$this->article->selectRow(" id=".$v['object_id']."");
				if(empty($a) or $a['status']>98 ){
					$this->love->delete("id=".$v['id']);
				}else{
					$sdata[]=$a;
				}
			}
		}
		$url="/index.php?m=article&a=mylove";
		$pagelist=$this->pagelist($rscount,$limit,$url); 
		$this->smarty->assign(array(
			"list"=>$sdata,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("article/mylove.html");
	}
	
	/*发表文章模型*/
	public function onAdd(){
		$cat_list=$this->category->children(0,MODEL_ARTICLE_ID);;
		$id=get('id','i');
		if($id){
			$data=$this->article->selectRow(array("where"=>"id=$id"));
			$t_d=$this->article_data->selectRow(array("where"=>"id=$id"));
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}		
		}else{
			 /*找到一个内的未添加*/
			$data=$this->article->selectRow(array("where"=>"is_temp=1 AND userid=".$this->login->userid));
			if(empty($data)){
				$id=$this->model_index->insert(array("tablename"=>"article"));
				$this->article->insert(array("id"=>$id,"dateline"=>time(),"is_temp"=>1,"status"=>99,"userid"=>$this->login->userid));
				$this->article_data->insert(array("id"=>$id,"dateline"=>time()));
				$this->article_attr->insert(array("attr_content"=>" ","dateline"=>time(),"id"=>$id));
				$data['id']=$id;
			}
		}
		$attr=$this->article_attr->selectRow(array("where"=>"id=$id "));
		$this->smarty->assign(
			array(
				"cat_list"=>$cat_list,
				"data"=>$data,
				"user_cat_list"=>$this->user_category->children(array("where"=>" tablename='article' AND pid=0 AND userid=".$this->login->userid)),
				"attr"=>$attr,
				"attr_cat"=>$this->attribute_cat->attr_cat(),
			)
		);
		$this->smarty->display("article/add.html");
	}
	
	public function onGetId(){
		if(!$this->login->userid){
			exit(json_encode(array("error"=>1,"nologin"=>1)));
		}else{
		$id=$this->model_index->insert(array("tablename"=>"article"));
		$this->article->insert(array("id"=>$id,"dateline"=>time(),"is_temp"=>1,"status"=>99,"userid"=>$this->login->userid));
		$this->article_data->insert(array("id"=>$id,"dateline"=>time()));
		$this->article_attr->insert(array("attr_content"=>" ","dateline"=>time(),"id"=>$id));
		}
		exit(json_encode(array("error"=>0,"id"=>$id)));
	}
	
	public function onSave(){
		$id=post('id','i');
		if($id){
			$row=$this->article->selectRow("id=".$id);
			if($row['is_temp']==1){
				$data['is_temp']=0;
				$data['status']=1;
			}
			if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],0,0,"/index.php");
		}
		$data['title']=post('title','h');
		if(empty($data['title'])) $this->goall('标题不能为空',1);
		$data['last_time']=time();			
		$data['keywords']=post('keywords','h');
		$data['description']=post('description','h');
		if(empty($data['description'])){			
			$data['description']=cutstr(strip_tags($_POST['content']),240);
		}
		$data['catid']=post('catid','i');
		$data['user_catid']=post('user_catid','i');
		$cat=$this->category->selectRow("catid=".$data['catid']);
		if(empty($cat)) $this->goall($this->lang['cat_empty'],1);
		$data['model_id']=$cat['model_id'];
		$data['imgurl']=post('imgurl','h');
		if($data['imgurl']){
			$data['is_img']=1;
		}else{
			$data['is_img']=0;
		}			
		if($data['imgurl']){
			$data['is_img']=1;			 
		}
		
		
		$sdata=array(
			"id"=>$id,
			"content"=>post('content','x'),
			"dateline"=>time()
		);
		/*扩展信息*/
		$post_att=post('attr');
		$col_attr=$this->attribute->col_name_attr(post('attr_cat_id','i'));
		if($col_attr){
			foreach($col_attr as $k=>$v){
				$data[$v]=intval($post_att[$k]);
			}
		}
		/*END 扩展信息*/
		/*属性处理*/
		$data['attr_cat_id']=post('attr_cat_id','i');		
		$attr_cat_id=post('attr_cat_id','i');		
		$attr_content=base64_encode(json_encode(stripslashes_deep(post('attr','x'))));
		/*End 属性*/
		if($id){			
			$this->article->update($data,array("id"=>$id));
			if(!$this->article_data->selectRow("id=$id")){
				$this->article_data->insert($sdata);
			}else{
				$this->article_data->update($sdata,array("id"=>$id));
			}
			if($this->article_attr->selectRow(array("where"=>" id=$id "))){
					$this->article_attr->update(array(
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id
					),"id=$id");
			}else{
					$this->article_attr->insert(array(
						"id"=>$id,
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id,
						"dateline"=>time()
					));
			}
		}else{
			$data['userid']=$this->login->userid;
			$data['dateline']=time();
			
			$data['id']=$id=$this->model_index->insert(array("tablename"=>"article"));
			$sdata['id']=$id;
			if($this->article->insert($data)){
				$this->article_data->insert($sdata);
			}
			$this->article_attr->insert(array(
						"id"=>$id,
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id,
						"dateline"=>time()
			));
		}
		//更新相关统计
		if($row['is_temp']==1){
			$this->user->changeNum("topic_num",1,"userid=".$this->userid);
			$user=$this->login->getUser();
			$this->category->update_new_topic($data['catid'],array("last_time"=>time(),"nickname"=>$user['nickname'],"userid"=>$this->userid,"title"=>$data['title'],"id"=>$id));
		}
		$this->goall($this->lang['save_success'],0,0,APPINDEX."?m=article&a=add&id=".$id);
	}
	
	public function onDelete(){
		$row=$this->article->selectRow("id=".$id);
		if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		$this->article->update(array("status"=>98),"id=".$id);
		$this->goall($this->lang['delete_success']);
	}
	
	public function onattrByCat(){
		 $id=get('id','i');
		 $cat=$this->category->selectRow(array("where"=>" catid=$id "));
		 if($cat['attr_cat_id']){
			$this->loadModel(array("attribute"));
		 	$cat_id=$cat['attr_cat_id'];
			$data=$this->attribute->getAttr($cat_id);
			$this->smarty->assign(array(
				"data"=>$data,
				"attr"=>$attr,
				"attr_cat_id"=>$cat_id
			));
			$this->smarty->display("attr/attr.html"); 
		 }
	 }
	 
	 
	 public function onAttr(){
		 $this->loadModel(array("attribute"));
		 $cat_id=get('cat_id','i');
		 $id=get('id','i');
		 $attr=array();
		 if($id){
			$row=$this->article_attr->selectRow(array("where"=>" id=$id AND attr_cat_id=".$cat_id." "));
			
			if($row){ 
				$attr=json_decode(base64_decode($row['attr_content']),true);
				 
			}
			 
		 }
		$data=$this->attribute->getAttr($cat_id);
		
		$this->smarty->assign(array(
			"data"=>$data,
			"attr"=>$attr,
			"attr_cat_id"=>$cat_id
		));
		$this->smarty->display("attr/attr.html");
		
	 }
	 
	 public function onAddClick(){
		$id=get_post('id','i');
		$row=$this->article->selectRow("id=".$id);
		if($row){
			$this->article->update(array("view_num"=>$row['view_num']+1),"id=".$id);
		}
		 
	 }
	
}
?>