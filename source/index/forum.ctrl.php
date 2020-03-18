<?php
class forumControl extends skymvc{
	public $userid;
	public $user;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","user","model","forum","forum_data","login","model_index","attribute_cat","attribute","forum_attr"));
		if(in_array(get('a'),array("my","save","add"))){
			$this->login->checklogin();
		}
		$this->userid=$this->login->userid;
	}
	
	function onDefault(){
		$rscount=true;
		$where=" status<10  ";
		$url="/index.php?m=forum";
		
		$start=get_post('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"last_time DESC"
		);
		$data=$this->forum->select($option,$rscount);
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
		$this->smarty->display("forum/index.html");
	}

	public function onHome(){
		$userid=get('userid','i');
		if(empty($userid)){
			$userid=$this->login->userid;
		}
		$user=$this->user->selectRow("userid=".$userid);
		if(empty($user)) $this->goall($this->lang['user_no_exists'],1,0,"/index.php");
		$limit=20;
		$where=" status<98 AND userid=".$userid."  ";
		$url="/index.php?m=forum&a=home&userid=".$userid;
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
		$data=$this->forum->select($option,$rscount);
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
				"user"=>$user
			)
		);
		$this->smarty->display("forum/home.html");
	}
	
	/*我的论坛模型*/
	public function onMy(){
		$this->login->checklogin();
		$limit=20;
		$where['userid']=$this->login->userid;;
		
		$where['status<']=99;
		$start=get('per_page','i');
		$option=array(
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC",
			"where"=>$where,
		);
		$rscount=true;
		$data=$this->forum->select($option,$rscount);
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
		$url="/index.php?m=forum&a=my";
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(
			array(
				"data"=>$data,
				"rscount"=>$rscount,
				"pagelist"=>$pagelist,
			)
		);
		$this->smarty->display("forum/my.html");
	}
	
	/*发表论坛模型*/
	public function onAdd(){
		$cat_list=$this->category->children(0,MODEL_FORUM_ID);;
		$id=get('id','i');
		if($id){
			$data=$this->forum->selectRow(array("where"=>"id=$id AND userid=".$this->login->userid));
			$t_d=$this->forum_data->selectRow(array("where"=>"id=$id"));
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}		
		}else{
			$data=$this->forum->selectRow(array("where"=>"is_temp=1 AND userid=".$this->login->userid." AND dateline>".(time()-3600)." "));
			if(empty($data)){
				$id=$this->model_index->insert(array("tablename"=>"forum"));
				$this->forum->insert(array("id"=>$id,"dateline"=>time(),"is_temp"=>1,"status"=>99,"userid"=>$this->login->userid));
				$this->forum_data->insert(array("id"=>$id,"dateline"=>time()));
				$this->forum_attr->insert(array("attr_content"=>" ","dateline"=>time(),"id"=>$id));
				$data['id']=$id;
			}
		}
		$this->smarty->assign(
			array(
				"cat_list"=>$cat_list,
				"data"=>$data,
				"attr_cat"=>$this->attribute_cat->attr_cat(),
			)
		);
		$this->smarty->display("forum/add.html");
	}
	
	public function onSave(){
		$id=post('id','i');
		if($id){
			$row=$this->forum->selectRow("id=".$id);
			if($row['is_temp']==1){
				$data['is_temp']=0;
				$data['status']=1;				
			}
			if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		}
		$data['title']=post('title','h');	
		$data['last_time']=time();		
		$data['keywords']=post('keywords','h');
		$data['description']=post('description','h');
		if(empty($data['description'])){			
			$data['description']=cutstr(strip_tags($_POST['content']),240);
		}
		$data['catid']=post('catid','i');
		$cat=$this->category->selectRow("catid=".$data['catid']);
		if(empty($cat)) $this->goall($this->lang['cat_empty']);
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
		
		//gps信息
		if(post('latlng')){
			$latlng=explode(",",post('latlng'));
			$data['lat']=round($latlng[0],6);
			$data['lng']=round($latlng[1],6);
			if ($data['lat'] && $data['lng']){
					$data['isgps']=1;
			}
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
			$this->forum->update($data,array("id"=>$id));
			if(!$this->forum_data->selectRow("id=$id")){
				$this->forum_data->insert($sdata);
			}else{
				$this->forum_data->update($sdata,array("id"=>$id));
			}
			if($this->forum_attr->selectRow(array("where"=>" id=$id "))){
					$this->forum_attr->update(array(
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id
					),"id=$id");
			}else{
					$this->forum_attr->insert(array(
						"id"=>$id,
						"attr_content"=>$attr_content,
						"attr_cat_id"=>$attr_cat_id,
						"dateline"=>time()
					));
			}
		}else{
			$data['userid']=$this->login->userid;
			$data['dateline']=time();
			
			$data['id']=$id=$this->model_index->insert(array("tablename"=>"forum"));
			$sdata['id']=$id;
			if($this->forum->insert($data)){
				$this->forum_data->insert($sdata);
			}
			$this->forum_attr->insert(array(
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
		$this->goall($this->lang['save_success'],0,"","/index.php?m=show&id=".$id);
	}
	
	public function onDelete(){
		$row=$this->forum->selectRow("id=".$id);
		if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		$this->forum->update(array("status"=>98),"id=".$id);
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
			$row=$this->forum_attr->selectRow(array("where"=>" id=$id AND attr_cat_id=".$cat_id." "));
			
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
		$row=$this->forum->selectRow("id=".$id);
		if($row){
			$this->forum->update(array("view_num"=>$row['view_num']+1),"id=".$id);
		}
		 
	 }
	
}
?>