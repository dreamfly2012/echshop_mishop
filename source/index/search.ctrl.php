<?php
class searchControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("model"));
	}
	
	public function onDefault(){
		$keyword=get_post('keyword','h');
		$type=get_post('type','h');
		$type=$type?$type:"article";
		$mod=$this->model->selectRow("tablename='".$type."'");
		$fun="on".$type;
		if(method_exists("searchControl",$fun)){
			
			$this->$fun();
			exit;
		}
		$modapi=$type."apiControl";
		$this->loadControl($type."api");
		$where=" status =2 ";
		$baseurl=$url="/index.php?m=search";
		$url.="&type=".$type;
		$this->loadClass("keywords",false,false);
		$word=new keywords(ROOT_PATH."config/dict/");
		$d=$word->save_all()->set("all")->get($keyword);
		if($keyword){
			$this->loadModel($type);
			$r=$this->$type->getCount($where." AND title like '%".$keyword."%' ");
			if(!$r && !empty($d)){
				$wor="";
				$k=0;
				foreach($d as $c){
					
					if($k==0){
						$wor=" title like '%".$c."%' ";
					}else{
						$wor.=" or title like '%".$c."%' ";
					}
					$k++;
				}
				$where.=" AND (".$wor.") ";
			}else{
				$where.=" AND title like '%".$keyword."%' ";
			}
			$url.="&keyword=".urlencode($keyword);
		}
		$pagesize=ISWAP?12:24;
		$start=get('per_page','i');
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$pagesize,
			"order"=>"id DESC",			
		);
		$rscount=true;
		$list=$this->$modapi->select($option,$rscount);
		if($list){
			foreach($list as $k=>$v){
				$v['title']=str_replace($keyword,'<span style="color:red;">'.$keyword.'</span>',$v['title']);
				$list[$k]=$v;
			}
		}
		$pagelist=$this->pagelist($rscount,$pagesize,$url);
		$this->smarty->assign(
			array(
			"baseurl"=>$baseurl,
			"seo"=>$seo,
			"data"=>$list,
			"pagelist"=>$pagelist,
			"rscount"=>$rscount,
			"pagesize"=>$pagesize,
			)
		);
		
		$this->smarty->display("search/index.html");
	}
	
	
	public function onForumx(){
		require ROOT_PATH."api/alisearch/init.php";
		// 实例化一个搜索类 search_obj
		$search_obj = new CloudsearchSearch($client);
		// 指定一个应用用于搜索
		$search_obj->addIndex($app_name);
		// 指定搜索关键词
		$keyword=get_post('keyword','h');
		
		$search_obj->setQueryString("index:'".$keyword."'");
		// 指定返回的搜索结果的格式为json
		$search_obj->setFormat("json");
		// 执行搜索，获取搜索结果
		$json = $search_obj->search();
		// 将json类型字符串解码
		$result = json_decode($json,true);
		if($result['status']=='OK'){
			$data=$result['result']['items'];
			if($data){
				foreach($data as $k=>$v){
					$uids[]=intval($v['userid']);
					$catids[]=intval($v['catid']);
					 
				}
				$cats=M("category")->cat_list(" catid in("._implode($catids).")");
				$us=M("user")->getUserByIds($uids);
				foreach($data as $k=>$v){
					$v['description']=$v['content'];
					$v['cname']=$cats[$v['catid']];
					$u=$us[$v['userid']];
					$v['nickname']=$u['nickname'];
					$v['user_head']=$u['user_head'];
					$data[$k]=$v;
				}
			}
		}
		$baseurl=$url="/index.php?m=search&type=forum";
		$this->smarty->assign(
			array(
			"baseurl"=>$baseurl,
			"seo"=>$seo,
			"data"=>$data,
			"pagelist"=>$pagelist,
			"rscount"=>$rscount,
			"pagesize"=>$pagesize,
			)
		);
		
		$this->smarty->display("search/index.html");
	}
	
	public function onUser(){
		$this->loadModel("user");
		$start=get('start','i');
		$limit=20;
		$url="/index.php?m=search&type=user";
		$keyword=get_post('keyword','h');
		$where=" status<99 ";
		if($keyword){
			$where .=" AND nickname like '%".$keyword."%' ";
			$url.="&keyword=".urlencode($keyword);
		}
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" grade DESC",
		);
		$rscount=true;
		$data=$this->user->select($option,$rscount);
		
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"pagelist"=>$pagelist,
			"rscount"=>$rscount
		));
		$this->smarty->display("search/user.html");
	}
	
	public function onShop(){
		$this->loadModel("shop");
		$start=get('start','i');
		$limit=20;
		$url="/index.php?m=search&type=shop";
		$keyword=get_post('keyword','h');
		$where=" status<99 ";
		if($keyword){
			$where .=" AND shopname like '%".$keyword."%' ";
			$url.="&keyword=".urlencode($keyword);
		}
		$option=array(
			"where"=>$where,
			"start"=>$start,
			"limit"=>$limit,
			"order"=>" grade DESC",
		);
		$rscount=true;
		$data=$this->shop->select($option,$rscount);
		
		$pagelist=$this->pagelist($rscount,$limit,$url);
		$this->smarty->assign(array(
			"data"=>$data,
			"pagelist"=>$pagelist,
			"rscount"=>$rscount
		));
		$this->smarty->display("search/shop.html");
	}
	
	 
	
}
?>