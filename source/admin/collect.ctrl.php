<?php
class collectControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("collect","collect_rule","model","model_index","category","collect_config","user"));
		$this->loadClass("stole");
		$this->loadClass("image");
		error_reporting(0);
		include_once(ROOT_PATH."api/ossapi/ossapi.php"); 
	}
	
	public function upload_oss($files){
		if(!UPLOAD_OSS) return false;
		if(empty($files)) return false;
		$arr=array("",".100x100.jpg",".small.jpg",".middle.jpg");
		foreach($arr as $a){		
			if(file_exists(ROOT_PATH.$files.$a)){
				$to=str_replace("//","/",$files.$a);
				$from=ROOT_PATH.$files.$a;
				$response = oss_upload_file(array("bucket"=>OSS_BUCKET,"to"=>$to,"from"=>$from));
				if(defined("UPLOAD_DEL") && UPLOAD_DEL){
					@unlink($from);
				}
			}
		}
	}
	
	public function onDefault(){
		switch(get('s_status')){
			case 1:
					$status=1;
				break;
			case 98:
					$status=98;
				break;
			default:
				$status=0;
				break;
				
		}
		$start=get_post('per_page','i');
		$limit=100;
		$where="   status=".$status;
		$url=APPADMIN."?m=collect&a=default&s_status=".get_post('s_status','i');
		$title=get_post('title','h');
		if($title){
			$where.=" AND title like '%".$title."%' ";
			$url.="&title=".urlencode($title);
		}
		
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>"id DESC"
		);
		$rscount=true;
		$data=$this->collect->select($option,$rscount);
		if($data){
			foreach($data as $k=>$v){
				$rule=$this->collect_rule->selectRow("id=".$v['rule_id']);
				if($rule){
					$v['cname']=$this->category->selectOne(array("where"=>"catid=".$rule['catid'],"fields"=>"cname"));
				}
				$data[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$limit,$url); 
		$this->smarty->assign(array(
			"data"=>$data,
			"rscount"=>$rscount,
			"pagelist"=>$pagelist,
			"rule_list"=>$this->collect_rule->id_title()
		));
		$this->smarty->display("collect/index.html");
	}
	
	public function onList(){
		$id=get_post('id','i');
		$row=$this->collect_rule->selectRow(array("where"=>"id=".$id));
		if(empty($row)){
			$this->goall("出错啦",1);
		}
		$url=str_replace("[page]",$row['now_page']*$row['pagesize'],$row['page_url']);
		if(!empty($row['dl_url'])){
			$this->stole->getContent($row['dl_url'].urlencode($url));
		}else{
			$this->stole->getContent($url);
		}
		$a=explode("\r\n",$row['list_rule']);
		if($row['now_page']==0){
			$this->collect_rule->update(array("now_page"=>1),"id=".$row['id']);
			$this->goall("采集完毕",1,0,APPADMIN."?m=collect");
		}
		$this->collect_rule->update(array("now_page"=>$row['now_page']-1),"id=".$row['id']);
		foreach($a as $k=>$v){
			if(!empty($v)){
					$t_d=explode("=>>",$v);	
					if($t_d[0]!=="replace"){		
						$arr=$this->parseRule($t_d[0],$t_d[1]);
					}else{
						$a=explode("=>",$t_d[1]);
						$replace_1[]=$a[0];
						$replace_2[]=$a[1];
					}
				}
		}
		
		if(isset($arr['title'][0])){
			if($this->stole->charset!="utf-8"){
				$arr=iconvstr($this->stole->charset,"utf-8",$arr);
			}
		}
		
		if(!empty($arr)){
			
			foreach($arr as $k=>$v){
				foreach($v as $kk=>$d){
					$newdata[$kk][$k]=$d;
				}
			}
			
			foreach($newdata as $k=>$v){
				$url=str_replace($replace_1,$replace_2,$this->builtlink($v['url'],$row['domain']));
				if(!$this->collect->selectRow("url='".$url."' ")){
					$data=array(
						"title"=>strip_tags($v['title']) ,
						"url"=>$url,
						"rule_id"=>$row['id'],
						"ruledata"=>base64_encode(json_encode($v)),
						"dateline"=>time()
					);
					$this->collect->insert($data);
				}
			}
			
			 
		}
		echo "正在采集...";
		$this->gourl(APPADMIN."?m=collect&a=list&id=".$id);
	}
	
	public function builtlink($url,$domain){
		if($url{0}=="/"){
			$d=parse_url($domain);
			return "http://".$d['host'].$url;
		}elseif(preg_match("/^http/i",$url)==false){
			return $domain.$url;
		}
		return $url;
	}
	
	public function onSaveById(){
		$id=get_post('id','i');
		$row=$this->collect->selectRow(array("where"=>" id=".$id));
		if(empty($row)){
			$this->goall("采集完毕",1,0,APPADMIN."?m=collect");
		}
		$row['ruledata']=json_decode(base64_decode($row['ruledata']),true);
		$rule=$this->collect_rule->selectRow(array("where"=>" id=".$row['rule_id']));
		$this->collect->update(array("status"=>1),"id=".$row['id']);
		if(!empty($rule['dl_url'])){
			$this->stole->getContent($rule['dl_url'].urlencode($row['url']));
		}else{
			$this->stole->getContent($row['url']);
		}
		 
		$a=explode("\r\n",$rule['content_rule']);
				
		foreach($a as $k=>$v){
			if(!empty($v)){
				$t_d=explode("=>>",$v);		
				$arr=$this->parseRule($t_d[0],$t_d[1]);
			}
		}
		
		if($this->stole->charset!="utf-8"){
				$arr=iconvstr($this->stole->charset,"utf-8",$arr);
		}
		
		if(is_array($arr)){
			$content=$arr['content'];
		}else{
			$content=$arr;
			unset($arr);
			$arr['content']=$content;
		}
		
		if(isset($arr['title'])){
			$title=$arr['title'];
		}else{
			$title=$row['title'];
		}
		
		if(isset($arr['imgurl'])){
			$imgurl=$arr['imgurl'];
		}elseif(isset($row['ruledata']['imgurl'])){
			$imgurl=$row['ruledata']['imgurl'];
		}else{
			$imgurl="";
		}
		if(!$imgurl){
			//do image
			preg_match_all("/<img[^>]*src[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$content,$imgs);
			if(isset($imgs[1]) && !empty($imgs[1])){
				$imgurl=$imgs[1][0];
				if(strpos($imgs[0][0],"data-original=")!==false){ 
					preg_match("/<img[^>]*data-original[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$imgs[0][0],$a);
					$imgurl=$a[1];
				}
			}
		}
		 
		$cat=$this->category->selectRow("catid=".$rule['catid']);
		if(empty($cat)){
			$this->goall($this->lang['category_empty'],0,0,APPADMIN."?m=collect");
		}
		$mod=$this->model->selectRow("id=".$cat['model_id']);
		if(empty($mod)){
			$this->goall($this->lang['category_empty'],0,0,APPADMIN."?m=collect");
		}
		$model=$mod['tablename'];
		$model_id=$mod['model_id'];
		$model_data=$model."_data";
		$this->loadModel(array($model,$model_data));
		$id=$this->model_index->insert(array("tablename"=>$model));
		
		//发布用户
		$config=$this->collect_config->selectRow();
		$t_u=explode(",",$config['pi_user']);
			if($t_u){
					foreach($t_u as $uid){
						$t_x=explode("-",$uid);
						if(isset($t_x[1])){
							for($i=$t_x[0];$i<=$t_x[1];$i++){
								$users[]=$i;
							}
						}else{
							$users[]=$uid;
						}
					}
					 shuffle($users);
					 if($users){
						$users=$this->user->selectCols(array("where"=>" userid in("._implode($users).") ","fields"=>"userid"));
						$uid=$users[0];
					 }else{
						$uid=1; 
					 }
					 
			}
		$title=addslashes($title);
		
		//替换图片
		$this->loadClass("upload");
		$dir="attach/product/".$this->upload->dirId($id);
		if($rule['remote_img']){
			$content=$this->remote_img($dir,$content);
		}
		if($rule['filter_img']){
				preg_match_all("/<img[^>]*src[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$content,$a);
				if(isset($a[1]) && !empty($a[1])){
					$content="";
					foreach($a[1] as $v){
						$content.="<span class='filter_img'><img src='".$v."'></span>"; 
					}
				}
			}
		$content=addslashes(removelink($content));	
		$data=array(
			"id"=>$id,
			"catid"=>$rule['catid'],
			"title"=>$title,
			"dateline"=>time(),
		
			"status"=>1,
			"userid"=>$uid,
			"model_id"=>$model_id,
			"shopid"=>$rule['shopid']
		);
		
		$data['last_time']=time();
		$data['grade']=50;//默认50分
		
		foreach($row['ruledata'] as $k=>$v){
			if(!in_array($k,array("title","imgurl","url"))){
				$data[$k]=$v;
			}
		}
		if(!isset($data['description'])){
			$data['description']=cutstr(ustrip_tags($content),240);
		}
		
		if(!isset($data['keywords'])){
			$data['keywords']=$title;	
		}
		if($title && $content){
			
			
			
			if($imgurl){
				$t_imgurl=$imgurl;
				$img=new image();
				$this->loadClass("upload");
				$dir="attach/product/".$this->upload->dirId($id);
				umkdir($dir);						 
				$imgurl=$dir.time().md5($t_imgurl).".jpg";
				app_file_put_contents($imgurl,app_file_get_contents($t_imgurl));
				$im=@getimagesize($imgurl);
				$mini_img=$img->makethumb($imgurl.".100x100.jpg",$imgurl,"100","100",1);
				$small_img=$img->makethumb($imgurl.".small.jpg",$imgurl,"240");
				$im=getimagesize($small_img);
				$middle_img=$img->makethumb($imgurl.".middle.jpg",$imgurl,"440");
				$big_img=$imgurl;
				$data['imgurl']=$imgurl;
				$data['thumbsinfo']=json_encode(array(
						"width"=>$im[0],
						"height"=>$im[1]
				));
				$this->upload_oss($imgurl);
				$data['is_img']=1;
			}
			$this->$model->insert($data);
			$sdata=array(
				"content"=>$content,
				"id"=>$id,
				
				"dateline"=>time()
			);
			$this->$model_data->insert($sdata);
			$this->collect->update(array("isvalid"=>1),"id=".$row['id']);
		}
		
		$this->smarty->assign(array(
			"content"=>"ID:$id $title ".stripslashes($content)
		)); 
		$this->smarty->display("collect/content.html");
	}
	
	public function onSave(){
		$rule_id=get_post('rule_id','i');
		$where=" status=0 ";
		if($rule_id){
			$where.=" AND   rule_id=".$rule_id;
			$rule=$this->collect_rule->selectRow(array("where"=>" id=".$rule_id));
		}else{
			$rule=$this->collect_rule->selectRow();
			$rule && $where.=" AND   rule_id=".$rule['id'];
		}
		
		$row=$this->collect->selectRow(array("where"=>$where));
		if(empty($row)){
			$this->goall("采集完毕",1,0,APPADMIN."?m=collect");
		}
		$row['ruledata']=json_decode(base64_decode($row['ruledata']),true);
		$this->collect->update(array("status"=>1),"id=".$row['id']);
		if(!empty($rule['dl_url'])){

			$this->stole->getContent($rule['dl_url'].urlencode($row['url']));
		}else{
			$this->stole->getContent($row['url']);
		}
		$a=explode("\r\n",$rule['content_rule']);		
		foreach($a as $k=>$v){
			if(!empty($v)){
				$t_d=explode("=>>",$v);		
				$arr=$this->parseRule($t_d[0],$t_d[1]);
			}
		}
		
		if($this->stole->charset!="utf-8"){
				$arr=iconvstr($this->stole->charset,"utf-8",$arr);
		}
		
		if(is_array($arr)){
			$content=$arr['content'];
		}else{
			$content=$arr;
			unset($arr);
			$arr['content']=$content;
		}
		
		if(isset($arr['title'])){
			$title=$arr['title'];
		}else{
			$title=$row['title'];
		}
		
		if(isset($arr['imgurl'])){
			$imgurl=$arr['imgurl'];
		}elseif(isset($row['ruledata']['imgurl'])){
			$imgurl=$row['ruledata']['imgurl'];
		}else{
			$imgurl="";
		}
		if(!$imgurl){
			//do image
			preg_match_all("/<img[^>]*src[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$content,$imgs);
			if(isset($imgs[1]) && !empty($imgs[1])){
				$imgurl=$imgs[1][0];
				if(strpos($imgs[0][0],"data-original=")!==false){ 
					preg_match("/<img[^>]*data-original[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$imgs[0][0],$a);
					$imgurl=$a[1];
				}
			}
		}
		$cat=$this->category->selectRow("catid=".$rule['catid']);
		if(empty($cat)){
			$this->goall($this->lang['category_empty'],0,0,APPADMIN."?m=collect");
		}
		$mod=$this->model->selectRow("id=".$cat['model_id']);
		if(empty($mod)){
			$this->goall($this->lang['category_empty'],0,0,APPADMIN."?m=collect");
		}
		$model=$mod['tablename'];
		$model_data=$model."_data";
		$this->loadModel(array($model,$model_data));
		$id=$this->model_index->insert(array("tablename"=>$model));
		//发布用户
		$config=$this->collect_config->selectRow(array());
		$t_u=explode(",",$config['pi_user']);
			if($t_u){
					foreach($t_u as $uid){
						$t_x=explode("-",$uid);
						if(isset($t_x[1])){
							for($i=$t_x[0];$i<=$t_x[1];$i++){
								$users[]=$i;
							}
						}else{
							$users[]=$uid;
						}
					}
					 shuffle($users);
					 if($users){
						$users=$this->user->selectCols(array("where"=>" userid in("._implode($users).") ","fields"=>"userid"));
						$uid=$users[0];
					 }else{
						$uid=1; 
					 }
					 
			}
		$title=addslashes($title);
		//替换图片
		$this->loadClass("upload");
		$dir="attach/product/".$this->upload->dirId($id);
		if($rule['remote_img']){
			$content=$this->remote_img($dir,$content);
		}
		if($rule['filter_img']){
				preg_match_all("/<img[^>]*src[^=]*=[^\"']*[\"'](.*)[\"'][^>]*>/iUs",$content,$a);
				if(isset($a[1]) && !empty($a[1])){
					$content="";
					foreach($a[1] as $v){
						$content.="<span class='filter_img'><img src='".$v."'></span>"; 
					}
				}
			}
		$content=addslashes(removelink($content));
		$data=array(
			"id"=>$id,
			"catid"=>$rule['catid'],
			"title"=>$title,
			"dateline"=>time(),
			"userid"=>$uid,
			
			"status"=>1,
			"model_id"=>$model_id,
			"shopid"=>$rule['shopid']
		);
		 
		$data['last_time']=time();
		$data['grade']=50;//默认50分
		if(!isset($data['description'])){
			$data['description']=cutstr(ustrip_tags($content),240);
		}
		
		if(!isset($data['keywords'])){
			$data['keywords']=$title;	
		}
		if($title && $content){
			
			if($imgurl){
				$t_imgurl=$imgurl;
				$img=new image();
				$this->loadClass("upload");
				$dir="attach/product/".$this->upload->dirId($id);
				umkdir($dir);						 
				$imgurl=$dir.time().md5($t_imgurl).".jpg";
				if(defined("SAE_GET") && SAE_GET){
					app_file_put_contents($imgurl,app_file_get_contents(SAE_GET.urlencode($t_imgurl)));
				}else{
					app_file_put_contents($imgurl,app_file_get_contents($t_imgurl));
				}
				$im=@getimagesize($imgurl);
				$mini_img=$img->makethumb($imgurl.".100x100.jpg",$imgurl,"100","100",1);
				$small_img=$img->makethumb($imgurl.".small.jpg",$imgurl,"240");
				$im=getimagesize($small_img);
				$middle_img=$img->makethumb($imgurl.".middle.jpg",$imgurl,"440");
				$big_img=$imgurl;
				$data['imgurl']=$imgurl;
				$data['thumbsinfo']=json_encode(array(
						"width"=>$im[0],
						"height"=>$im[1]
				));
				$data['is_img']=1;
				$this->upload_oss($imgurl);
			}
			$this->$model->insert($data);
			$sdata=array(
				"content"=>$content,
				"id"=>$id,
				
				"dateline"=>time()
			);
			$this->$model_data->insert($sdata);
			$this->collect->update(array("isvalid"=>1),"id=".$row['id']);
		}
		$content="ID: $id ".$title.$content.'
		<script language="javascript">
			setTimeout(function(){
				window.location.reload();
			},100);
		</script>
		';
		
		$this->smarty->assign(array(
			"content"=>stripslashes($content)
		)); 
		$this->smarty->display("collect/content.html");
	}
	
	public function onPiDelete(){
		$ids=post('ids','i');
		if($ids) $this->collect->delete("id in("._implode($ids).")");
		$this->goall($this->lang['delete_success']);
	}
	public function parseRule($t,$rule){
		switch($t){
				case "c":
						return $this->stole->cutHtml($rule);
					break;
				case "a":
						return $this->stole->preg_all($rule);
					break;
				case "r":
						return $this->stole->removeHtml($rule);
					break;	
				case "rp":
							return $this->stole->removePreg($rule);
						break;	
					
		}
	}
	
	public function onStatus(){
		$id=get_post('id','i');
		$status=98;
		$this->collect->update(array("status"=>98),"id=".$id);
		$this->goall("禁止成功");
	}
	
	public function remote_img($dir="",$content=''){
		$content=$content?$content:$this->content;
		preg_match_all("/<img.*src=[\'\"]+(.*)[\'\"][^>]*>/iUs",$content,$arr);
		$pics=$arr[1];
		
		if(empty($pics)) return $content;
		$dir=$dir?$dir:"attach/content/".date("Y/m/");
		umkdir($dir);
		foreach($pics as $k=>$pic)
		{
			$file=$dir.md5(time().$pic).".jpg";
			file_put_contents($file,curl_get_contents($pic));
			if(UPLOAD_OSS){
				$content=str_replace($pic,IMAGES_SITE.$file,$content);
				$this->upload_oss($file);
			}else{
				$content=str_replace($pic,"/".$file,$content);
			}
		}
		return $content;
	}
}

?>