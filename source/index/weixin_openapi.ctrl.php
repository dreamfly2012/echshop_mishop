<?php
class weixin_openapiControl extends skymvc{
	public $wx;
	public $fromUsername;
	public $toUsername;
	public $MsgId;
	public $MsgType;
	public $reply_id=0;
	public function __construct(){
		parent::__construct();
		$wid=get_post('wid','i');
		if($wid){
			$where=" id=".$wid;
		}else{
			$where="";
		}
		$this->loadModel(array("weixin","weixin_command","weixin_menu","weixin_reply","weixin_user","weixin_sucai"));
		$this->wx=$this->weixin->selectRow(array("where"=>$where,"order"=>"id DESC"));

		define("WXTOKEN",$this->wx['token']);
	}
	
	
	public function onDefault(){
		if($this->wx['status']){
 			$this->responseMsg();
		}else{
			$this->valid();
		}
		
	}
	/*素材显示*/
	function wx_sucai($id){
		$data=$this->weixin_sucai->selectRow("id=".$id." ");
		 
		if($data){
			$child=$this->weixin_sucai->getAll("SELECT * FROM ".table('weixin_sucai')." WHERE pid=".$id." ");
			$content="<item>
		<Title><![CDATA[".$data['title']."]]></Title> 
		<Description><![CDATA[".$data['description']."]]></Description>
		<PicUrl><![CDATA[".IMAGES_SITE.$data['imgurl']."]]></PicUrl>
		<Url><![CDATA[".($data['linkurl']?$data['linkurl']:"http://".$_SERVER['HTTP_HOST']."/index.php?m=weixin_sucai&a=show&id=".$data['id'])."]]></Url>
		</item>";
			if($child){
				foreach($child as $r){
					$content.="<item>
		<Title><![CDATA[".$r['title']."]]></Title> 
		<Description><![CDATA[".$r['description']."]]></Description>
		<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl']."]]></PicUrl>
		<Url><![CDATA[".($r['linkurl']?$r['linkurl']:"http://".$_SERVER['HTTP_HOST']."/index.php?m=weixin_sucai&a=show&id=".$r['id'])."]]></Url>
		</item>\n";
				}
			}
				$textTpl = "<xml>
						<ToUserName><![CDATA[".$this->fromUsername."]]></ToUserName>
						<FromUserName><![CDATA[".$this->toUsername."]]></FromUserName>
						<CreateTime>".time()."</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						<ArticleCount>".(count($child)+1)."</ArticleCount>
						<Articles>".$content."</Articles>				 
					</xml>";
			echo $textTpl;
			exit;		
		}else{
			wx_error();
		}
	}
	
	
	/*图文显示*/
	
	function wx_tuwen($data){
		if(empty($data) or !is_array($data)){
			if(empty($data)){
				$data="无法回复您的问题";
			}
			$textTpl = "<xml>
								<ToUserName><![CDATA[".$this->fromUsername."]]></ToUserName>
								<FromUserName><![CDATA[".$this->toUsername."]]></FromUserName>
								<CreateTime>".time()."</CreateTime>
								<MsgType><![CDATA[text]]></MsgType>
								<Content><![CDATA[".$data."]]></Content> 
								<FuncFlag>0</FuncFlag>
								</xml>"; 
			echo  $textTpl;
			exit;
		}
		$content="";
		foreach($data as $r){
				  $content.="<item>
		<Title><![CDATA[".$r['title']."]]></Title> 
		<Description><![CDATA[".$r['description']."]]></Description>
		<PicUrl><![CDATA[".$r['imgurl']."]]></PicUrl>
		<Url><![CDATA[".$r['url']."]]></Url>
		</item>\n";
				}
	 
		$textTpl = "<xml>
						<ToUserName><![CDATA[".$this->fromUsername."]]></ToUserName>
						<FromUserName><![CDATA[".$this->toUsername."]]></FromUserName>
						<CreateTime>".time()."</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						<ArticleCount>".count($data)."</ArticleCount>
						<Articles>".$content."</Articles>				 
					</xml>";
		echo  $textTpl;
		exit;
	}

	
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	
	public function post(){
		$textTpl="<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName> 
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[%s]]></MsgType>
 <Content><![CDATA[%s]]></Content>
 <MsgId>0</MsgId>
 </xml>";
	}

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $this->fromUsername=$fromUsername = $postObj->FromUserName;
                $this->toUsername=$toUsername = $postObj->ToUserName;
                $keyword=$content = trim($postObj->Content);
				$this->MsgId=$MsgId=$postObj->MsgId;
				$this->MsgType=$MsgType=$postObj->MsgType;
				$picurl=$postObj->PicUrl;
				$MediaId=$postObj->MediaId;
				$ThumbMediaId=$postObj->ThumbMediaId;
				$format=$postObj->Format;
				$location_x=$postObj->Location_X;
				$location_y=$postObj->Location_y;
				$scale=$postObj->Scale;
				$label=$postObj->Label;
				$title=$postObj->Title;
				$descripton=$postObj->Description;
				$url=$postObj->Url;
				$event=$postObj->Event;
				$EventKey=$postObj->EventKey;
				//插入响应数据
				$redata=array(
					"openid"=>$toUsername,
					"msgtype"=>$MsgType,
					"content"=>$content,
					"msgid"=>$MsgId,
					"picurl"=>$picurl,
					"mediaid"=>$MediaId,
					"thumbmediaid"=>$ThumbMediaId,
					"format"=>$format,
					"location_x"=>$location_x,
					"location_y"=>$location_y,
					"scale"=>$scale,
					"label"=>$label,
					"title"=>$title,
					"description"=>$descripton,
					"url"=>$url,
					"createtime"=>time(),
					
					"wid"=>WID,
					"fromusername"=>$fromUsername,
					"tousername"=>$toUsername,
					
				);
				
				$this->reply_id=$this->weixin_reply->insert($redata);
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>";  
				//处理订阅与取消订阅
				if($MsgType=='event'){
					$user=$this->weixin_user->selectRow(array("where"=>"openid='".$toUsername."' AND wid=".$this->wx['id']));
					if($event=='subscribe'){
						if($user){
							$this->weixin_user->update(array(
								"openid"=>$toUsername,
								"add_time"=>time(),
								"last_time"=>time(),
								"status"=>1,
							),"openid='".$toUsername."' AND wid=".$this->wx['id']);
						}else{
							$this->weixin_user->insert(array(
								"openid"=>$toUsername,
								"dateline"=>time(),
								"add_time"=>time(),
								"last_time"=>time(),
								"status"=>1,
								
								"wid"=>$this->wx['id']
							));
						}
						//回复订阅
						$row=$this->weixin_command->selectRow(array("where"=>" type_id=8 AND wid=".$this->wx['id']));
						if($row['sc_id']){
							$this->wx_sucai($row['sc_id']);
						}elseif($row['content']){
							$this->wx_tuwen($row['content']);
						}else{
							$this->wx_tuwen("感谢您的关注！");
						
						}
					}elseif($event=='unsubscribe'){
						if($user){
							$this->weixin_user->update(array(
								"openid"=>$toUsername,
								"del_time"=>time(),
								"last_time"=>time(),
								"status"=>0,
							),"openid='".$toUsername."' AND wid=".$this->wx['id']);
						}else{
							$this->weixin_user->insert(array(
								"openid"=>$toUsername,
								"dateline"=>time(),
								"del_time"=>time(),
								"last_time"=>time(),
								"status"=>0,
								
								"wid"=>$this->wx['id']
							));
						}
					}elseif($event=='CLICK'){
						$this->ClickReply($EventKey,$fromUsername, $toUsername);
						
					}elseif($event=='SCAN'){//二维码扫描
						$ticket=$postObj->Ticket;
						$this->qrsceneReply($EventKey,$ticket,$fromUsername,$toUsername);				
					} 
					$row=$this->weixin_command->selectRow(array("where"=>" type_id=8  AND wid=".$this->wx['id']));
					if($row['sc_id']) $this->wx_sucai($row['sc_id']);
					$this->wx_tuwen($row['content']);
					exit;
						
				}elseif($MsgType=='location'){
					$lat=$postObj->lat;
					$lng=$postObj->lng;
					$this->locationReply($lat,$lng,$fromUsername,$toUsername);
				}elseif($MsgType=='voice'){
					$this->voiceReply($MediaId,$fromUsername,$toUsername);
				}elseif($MsgType=='video'){
					$this->videoReply($MsgId,$ThumbMediaId,$fromUsername,$toUsername);
				}elseif($MsgType=='picture'){
					$this->pictureReply($picurl,$MediaId,$fromUsername,$toUsername);
				}
				//End
				
				//处理用户
				$user=$this->weixin_user->selectRow(array("where"=>"openid='".$toUsername."' AND wid=".$this->wx['id']));
				if(empty($user)){
					$this->weixin_user->insert(array(
						"openid"=>$toUsername,
						"dateline"=>time(),
						"last_time"=>time(),
						"reply_num"=>1,
						"wid"=>$this->wx['id'], 
						
					));
				
				}else{
					$this->weixin_user->update(array(
						"last_time"=>time(),
						"reply_num"=>$user['reply_num']+1,
					),"openid='".$toUsername."' AND wid=".$this->wx['id']);
				}
				//插入回复
			 	
                $time = time();
                           
				if(!empty( $keyword ))
                {
              		$msgType = "text";
                	$arr = $this->getContent($keyword);
					$contentStr=$arr['content'];
					if($arr['error']==0){
						$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
					}
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType);
					$resultStr=str_replace("[@content@]",$contentStr,$resultStr);
					$resultStr=str_replace(array("性交","乱伦","性欲望"),"",$resultStr);
                	echo $resultStr;
					exit;
                }else{
					$row=$this->weixin_command->selectRow(array("where"=>" type_id=8 AND wid=".$this->wx['id']));
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType);
					$contentStr="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$row['content']."]]></Content>";
					$resultStr=str_replace("[@content@]",$contentStr,$resultStr);
					exit;
                }
				

        }else {
        	echo "";
        	exit;
        }
    }
	/*菜单点击事件*/
	public function clickReply($EventKey,$fromUsername, $toUsername){
		$row=$this->weixin_menu->selectRow("wid=".$this->wx['id']." AND w_key='".addslashes($EventKey)."'");
		$this->wx_sucai($row['sc_id']);
	}
	
	/*图片消息*/
	public function pictureReply($PicUrl,$MediaId,$fromUsername, $toUsername){
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是图片消息".$EventKey."]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
	}
	/*语音*/
	public function voiceReply($MsgID,$fromUsername, $toUsername){
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是语音消息".$EventKey."]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
	}
	
	/*视频*/
	public function videoReply($MsgID,$ThumbMediaId,$fromUsername, $toUsername){
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是视频消息".$EventKey."]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
	}
	
	/*二维码扫描*/
	public function qrsceneReply($EventKey,$Ticket,$fromUsername, $toUsername){
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是二维码扫描事件".$EventKey."]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
	}
	
	/*地理位置事件*/
	public function locationReply($lat,$lng,$fromUsername, $toUsername){
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是地理位置事件]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
	}
	
	public function reply($config=array()){
		$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
	}
	public function getContent($keyword){
		$s=explode(":",$keyword);
		$row=$this->weixin_command->selectRow(array("where"=>" command='".$s[0]."' AND wid=".$this->wx['id']));
		if($row['fun'] && method_exists("weixin_openapiControl",$row['fun'])){
			
			return $this->$row['fun'](str_replace($s[0].":","",$keyword));
			
			exit;
		}
		switch($row['type_id']){
			case 1:
			case 7:
			case 8:
					if($row['sc_id']) $this->wx_sucai($row['sc_id']);
					$this->wx_tuwen($row['content']);
				break;
				
			
			
			default:
					$row=$this->weixin_command->selectRow(array("where"=>" isdefault=1 AND wid=".$this->wx['id']));
					if($row['fun'] && method_exists("weixin_openapiControl",$row['fun'])){
						return $this->$row['fun'](str_replace($s[0].":","",$keyword));
						exit;
					}
					if($row['sc_id']) $this->wx_sucai($row['sc_id']);
					$this->wx_tuwen($row['content']);
				break;
		}
		 
		
	}
	
	/*默认回复*/
	public function defaultReply($keyword){
		$row=$this->weixin_command->selectRow(array("where"=>" type_id=7 AND wid=".$this->wx['id']));
		if($row['fun'] && method_exists("weixin_openapiControl",$row['fun'])){
			return $this->$row['fun'](str_replace($s[0].":","",$keyword));
			exit;
		}
		if($row['sc_id']) $this->wx_sucai($row['sc_id']);
		$this->wx_tuwen($row['content']);
	}
	/*喜帖*/
	public function xitie($keyword){
		$arr=explode(":",$keyword);
		$content="http://".$_SERVER['HTTP_HOST']."/index.php?m=xitie&a=show&id=".$arr[0]."&sname=".urlencode($arr[1]);
		return array(
			"error"=>0,
			"content"=>"<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$content."]]></Content>"
		);
	}
	
	public function zufang($keyword=""){
		$this->loadModel(array("zufang","zufang_data"));
		$limit=10;
		$row=$this->zhufang->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->zhufang->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function hunsha($keyword=""){
		$this->loadModel(array("hunsha","hunsha_data"));
		$limit=10;
		$row=$this->hunsha->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->hunsha->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function meifa($keyword=""){
		$this->loadModel(array("meifa","meifa_data"));
		$limit=10;
		$row=$this->meifa->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->meifa->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function jiaju($keyword=""){
		$this->loadModel(array("jiaju","jiaju_data"));
		$limit=10;
		$row=$this->jiaju->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->jiaju->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	
	public function article($keyword=""){
		$this->loadModel(array("article","article_data"));
		$limit=10;
		$row=$this->article->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->article->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function picture($keyword=""){
		$this->loadModel(array("picture","picture_data"));
		$limit=10;
		$row=$this->picture->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->picture->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function product($keyword=""){
		$this->loadModel(array("product","product_data"));
		$limit=10;
		$row=$this->product->select(array("where"=>" title='".$keyword."' AND is_img=1 ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->product->select(array("where"=>" title like '%".$keyword."%'  AND is_img=1  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function caipu($keyword=""){
		$this->loadModel(array("caipu","caipu_data"));
		$where=" status<98 AND is_img=1 ";
		//解析条件
		
		$this->loadClass("keywords",false,false);
		$word=new keywords(ROOT_PATH."config/dict/");
		$d=$word->save_all()->set("all")->get($keyword);
		$this->slog("xadd23");
		if($keyword){
			
			$r=$this->caipu->getCount($where." AND title like '%".$keyword."%' ");
			if(!$r && !empty($d)){
				$wor="";
				$k=0;
				foreach($d as $c){
					
					if($k==0){
						$wor=" title like '%".$c."%' ";
					}else{
						$wor.=" AND title like '%".$c."%' ";
					}
					$k++;
				}
				$where.=" AND (".$wor.") ";
			}else{
				$where.=" AND title like '%".$keyword."%' ";
			}
			$url.="&keyword=".urlencode($keyword);
		}
		
		//ENd 条件
		
		$limit=10;
		$row=$this->caipu->select(array("where"=>$where,"order"=>"id DESC","limit"=>$limit));
		if(!empty($row)){
			$data=array();
			foreach($row as $r){
				$data[]=array(
					"title"=>$r['title'],
					
					"imgurl"=>IMAGES_SITE.$r['imgurl'].".middle.jpg",
					"url"=>"http://".$_SERVER['HTTP_HOST'].R("/index.php?m=show&id=".$r['id'])
				);
			}
			$this->weixin_reply->update(array("status"=>1)," id=".$this->reply_id);
			$this->wx_tuwen($data);
		}else{
			$this->defaultReply($keyword);
		}
	}
	
	public function jiemeng($keyword=""){
		$this->loadModel(array("mingli_dream","mingli_dream_log"));
		$row=$this->mingli_dream->selectRow(" title='".$keyword."' ");
		if(empty($row)){
			$row=$this->mingli_dream->selectRow(" title like '%".$keyword."%' ");
		}
		//第一次为空 反向继续查
		if(empty($row)){
			$rscount=false;
			$w=$this->mingli_dream->selectCols(array("fields"=>"title","limit"=>9999),$rscount,1,60);
			$preg=implode("|",str_replace(array(",","、"),"|",$w));
			/*倒叙排列*/
			$x=explode("|",$preg);
			$temp=array();
			foreach($x as $v){
				$v=trim($v);
				$temp[strlen($v)][]=$v;
			}
			$arr=array();
			krsort($temp);
			if($temp){
				foreach($temp as $d){
					foreach($d as $c){
						$arr[]=$c;
					}
				}
			}
			$preg=implode("|",$arr);
			/*倒序排列*/
			//file_put_contents("log.txt",$preg);
			preg_match("/($preg)/iUs",$keyword,$arr);
			if(!empty($arr)){
				$row=$this->mingli_dream->selectRow(" title='".$arr[0]."' ");
				if(empty($row)){
					$row=$this->mingli_dream->selectRow(" title like '%".$arr[0]."%' ");
				}
			}
		}
		if(!empty($row)){
			//插入解梦日志
			$this->mingli_dream_log->insert(array(
				"dream_id"=>$row['id'],
				
				"dateline"=>time(),
				"content"=>$keyword
			));
			if($row['imgurl']){
			$content="
				 <MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[".$keyword."]]></Title> 
<Description><![CDATA[".$row['detail']."]]></Description>
<PicUrl><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=jiemeng&a=img&keyword=".urlencode($keyword)."&imgurl=".$row['imgurl']."]]></PicUrl>
<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=jiemeng&a=tags&keyword=".urlencode($keyword)."]]></Url>
</item>
</Articles> 
			";
			}else{
				$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$keyword."\r\n".$row['detail']."]]></Content>";
			}
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到您的梦境！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = WXTOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	function slog($cc){
		file_put_contents("log.txt",$cc);
	}
	
}
?>