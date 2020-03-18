<?php
class goldgoodsControl extends skymvc{
	public $userid;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("category","model","goldgoods","goldgoods_data","login","model_index","user","user_category","goldgoods_user"));
		if(in_array(get('a'),array("my","save","add","mylove"))){
			$this->login->checklogin();
		}
		$this->userid=$this->login->userid;
		if($this->login->userid){
			$row=$this->goldgoods_user->selectRow("userid=".$this->login->userid);
			if(empty($row)){
				$this->goldgoods_user->insert(array("userid"=>$this->login->userid));
			}
		}
	}
	
	function onDefault(){
		$rscount=true;
		$where=" status=2  ";
		$url="/index.php?m=goldgoods";
		
		$start=get_post('per_page','i');
		$limit=20;
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"last_time DESC"
		);
		$data=$this->goldgoods->select($option,$rscount);
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
		$this->smarty->display("goldgoods/index.html");
		 
	}
	
	/*附近的图片*/
	public function onNear(){
		$this->smarty->display("goldgoods/near.html");
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
		$url="/index.php?m=goldgoods&a=home&userid".$userid;
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
		$data=$this->goldgoods->select($option,$rscount);
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
		$this->smarty->display("member/goldgoods_home.html");
	}
	
	/*我的金币兑换模型*/
	public function onMy(){
		$this->login->checklogin();
		$limit=20;
		$where=" status<99 AND userid=".$this->login->userid;;
		$url="/index.php?m=goldgoods&a=my";
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
		$data=$this->goldgoods->select($option,$rscount);
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
				"pid_list"=>$this->user_category->children(array("where"=>" tablename='goldgoods' AND pid=0 AND userid=".$this->login->userid))
			)
		);
		$this->smarty->display("member/goldgoods_my.html");
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
			"where"=>" userid=".$userid." AND tablename='goldgoods' ",
		);
		$rscount=true;
		$data=$this->love->select($option,$rscount);
		 
		if($data){
			foreach($data as $k=>$v){
				$a=$this->goldgoods->selectRow(" id=".$v['object_id']."");
				if(empty($a) or $a['status']>98 ){
					$this->love->delete("id=".$v['id']);;
				}else{
					$sdata[]=$a;
				}
			}
		}
		$url="/index.php?m=goldgoods&a=mylove";
		$pagelist=$this->pagelist($rscount,$limit,$url); 
		$this->smarty->assign(array(
			"list"=>$sdata,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist
		));
		$this->smarty->display("goldgoods/mylove.html");
	}
	
	/*发表金币兑换模型*/
	public function onAdd(){
		$cat_list=$this->category->children(0,MODEL_GOLDGOODS_ID);;
		$id=get('id','i');
		if($id){
			$data=$this->goldgoods->selectRow(array("where"=>"id=$id"));
			$t_d=$this->goldgoods_data->selectRow(array("where"=>"id=$id"));
			if(!empty($t_d)){
				$data=array_merge($data,$t_d);
			}		
		}else{
			$data=$this->goldgoods->selectRow(array("where"=>"is_temp=1 AND userid=".$this->login->userid." AND dateline>".(time()-3600)." "));
			if(empty($data)){
				$id=$this->model_index->insert(array("tablename"=>"goldgoods"));
				$this->goldgoods->insert(array("id"=>$id,"dateline"=>time(),"is_temp"=>1,"status"=>99,"userid"=>$this->login->userid));
				$this->goldgoods_data->insert(array("id"=>$id,"dateline"=>time()));
				$data['id']=$id;
			}
		}
		$this->smarty->assign(
			array(
				"cat_list"=>$cat_list,
				"data"=>$data,
				"user_cat_list"=>$this->user_category->children(array("where"=>" tablename='goldgoods' AND pid=0 AND userid=".$this->login->userid))
			)
		);
		$this->smarty->display("member/goldgoods_add.html");
	}
	
	public function onSave(){
		$id=post('id','i');
		if($id){
			$row=$this->goldgoods->selectRow("id=".$id);
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
		
		/*END 扩展信息*/
		if($id){			
			$this->goldgoods->update($data,array("id"=>$id));
			if(!$this->goldgoods_data->selectRow("id=$id")){
				$this->goldgoods_data->insert($sdata);
			}else{
				$this->goldgoods_data->update($sdata,array("id"=>$id));
			}
		}else{
			$data['userid']=$this->login->userid;
			$data['dateline']=time();
			
			$data['id']=$id=$this->model_index->insert(array("tablename"=>"goldgoods"));
			$sdata['id']=$id;
			if($this->goldgoods->insert($data)){
				$this->goldgoods_data->insert($sdata);
			}
		}
		//更新相关统计
		if($row['is_temp']==1){
			$this->user->changeNum("topic_num",1,"userid=".$this->userid);
			$user=$this->login->getUser();
			$this->category->update_new_topic($data['catid'],array("last_time"=>time(),"nickname"=>$user['nickname'],"userid"=>$this->userid,"title"=>$data['title'],"id"=>$id));
		}
		$this->goall($this->lang['save_success'],0,$data,APPINDEX."?m=goldgoods&a=add&id=".$id);
	}
	
	public function onDelete(){
		$row=$this->goldgoods->selectRow("id=".$id);
		if($row['userid']!=$this->login->userid) $this->goall($this->lang['die_access'],1,0,"/index.php");
		$this->goldgoods->update(array("status"=>98),"id=".$id);
		$this->goall($this->lang['delete_success']);
	}
	
	public function onAddClick(){
		$id=get_post('id','i');
		$row=$this->goldgoods->selectRow("id=".$id);
		if($row){
			$this->goldgoods->update(array("view_num"=>$row['view_num']+1),"id=".$id);
		}
		 
	 }
	
}
?>