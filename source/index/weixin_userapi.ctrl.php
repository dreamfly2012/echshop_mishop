<?php
class weixin_userapiControl extends skymvc{
	public $wx;
	public $fromUsername;
	public $toUsername;
	public $MsgId;
	public $MsgType;
	public function __construct(){
		parent::__construct();
		$this->loadModel(array("weixin","weixin_command","weixin_reply","weixin_user","domain"));
		$a=explode(".",$_SERVER['HTTP_HOST']);
		$domain=$a[0];
		$row=$this->domain->selectRow("domain='".$domain."'");
		$this->wx=$this->weixin->selectRow(array("where"=>" userid=".$row['userid']));
		define("WXTOKEN",$this->wx['token']);
	}
	
	
	public function onDefault(){
		if($this->wx['status']){
 			$this->responseMsg();
		}else{
			$this->valid();
		}
		
	}
	public function onToken(){
		$c=file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx244d799518c41ac1&secret=09ec9af9e9c4d5d8f5a59528db03cdc5");
		$data=json_decode($c,true);
		$token=$data['access_token'];
		$menu=$this->zh_json_encode(array(
			"button"=>array(
				array("type"=>"click","name"=>"Top10","key"=>"top10") ,
				array("type"=>"click","name"=>"哈哈哈","key"=>"aadd"),
				array("type"=>"view","name"=>"百度","url"=>"http://www.baidu.com","key"=>"aadd") ,
				
			)
		));
		echo $menu;
		$res=curl_post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token,$menu);
		print_r($res);
	}
	
	public function zh_json_encode($data){
		$data=self::array_urlencode($data);
		echo $data=json_encode($data);
		$data=self::array_urldecode($data);
		return $data;
	}
	
	public function array_urlencode($data){
		if(is_array($data)){
			foreach($data as $k=>$v){
				$data[$k]=self::array_urlencode($v);
			}
			return $data;
		}else{
			return urlencode($data);
		}
	}
	
	public function array_urldecode($data){
		if(is_array($data)){
			foreach($data as $k=>$v){
				$data[$k]=self::array_urldecode($v);
			}
			return $data;
		}else{
			return urldecode($data);
		}
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
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $MsgType);
					$contentStr="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$row['content']."]]></Content>";
					echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
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
					
					"wid"=>$this->wx['id']
					
				);
				//处理用户
				$user=$this->weixin_user->selectRow(array("where"=>"openid='".$toUsername."' AND wid=".$this->wx['id']));
				if(empty($user)){
					/*高级接口
					$acc=file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET");
					$user=file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".WXTOKEN."&openid=".$toUsername);
					file_put_contents("log.txt",json_encode($user).WXTOKEN.$toUsername);
					$user=json_decode($user,true);
					if(isset($user['nickname'])){
						$in_data=array(
							"openid"=>$toUsername,
							"dateline"=>time(),
							"add_time"=>$user['subscribe_time'],
							"last_time"=>time(),
							
							"nickname"=>$user['nickname'],
							"sex"=>$user['sex'],
							"city"=>$user['city'],
							"country"=>$user['country'],
							"province"=>$user['province'],
							"user_head"=>$user['headimgurl'],
						);
						$this->weixin_user->insert($in_data);
					}
					*/
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
						$redata['status']=1;
					}
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType);
					$resultStr=str_replace("[@content@]",$contentStr,$resultStr);
					$resultStr=str_replace(array("性交","乱伦","性欲望"),"",$resultStr);
                	echo $resultStr;
					
					$rid=$this->weixin_reply->insert($redata);
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
		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>".time()."</CreateTime>
							[@content@]
							<FuncFlag>0</FuncFlag>
							</xml>"; 
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername);
						$contentStr="<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[这是菜单点击测试哦".$EventKey."]]></Content>";
		echo $resultStr=str_replace("[@content@]",$contentStr,$resultStr);
		exit;
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
		if($row['fun'] && method_exists("weixin_userapiControl",$row['fun'])){
			
			return $this->$row['fun'](str_replace($s[0].":","",$keyword));
			exit;
		}
		switch($row['type_id']){
			case 1:
			case 7:
					return array(
						"error"=>0,
						"content"=>"<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$row['content']."]]></Content>"
					);
				break;
				
			case 2:
					
				break;
			
			default:
					$row=$this->weixin_command->selectRow(array("where"=>" isdefault=1 AND wid=".$this->wx['id']));
					if($row['fun'] && method_exists("weixin_userapiControl",$row['fun'])){
						return $this->$row['fun'](str_replace($s[0].":","",$keyword));
						exit;
					}
					return array(
						"error"=>1,
						"content"=>"<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$row['content']."]]></Content>"
					);
				break;
		}
		 
		
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
		$row=$this->zhufang->select(array("where"=>" title='".$keyword."' AND is_img=1 AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->zhufang->select(array("where"=>" title like '%".$keyword."%'  AND status<98 AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到你所在位置的租房信息！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
	
	public function hunsha($keyword=""){
		$this->loadModel(array("hunsha","hunsha_data"));
		$limit=10;
		$row=$this->hunsha->select(array("where"=>" title='".$keyword."' AND is_img=1  AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->hunsha->select(array("where"=>" title like '%".$keyword."%' AND status<98  AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到你要找的数据！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
	
	public function meifa($keyword=""){
		$this->loadModel(array("meifa","meifa_data"));
		$limit=10;
		$row=$this->meifa->select(array("where"=>" title='".$keyword."' AND is_img=1  AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->meifa->select(array("where"=>" title like '%".$keyword."%'  AND status<98 AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到你要找的数据！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
	
	public function jiaju($keyword=""){
		$this->loadModel(array("jiaju","jiaju_data"));
		$limit=10;
		$row=$this->jiaju->select(array("where"=>" title='".$keyword."' AND is_img=1  AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->jiaju->select(array("where"=>" title like '%".$keyword."%'  AND status<98 AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到你要找的数据！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
	
	public function picture($keyword=""){
		$this->loadModel(array("picture","picture_data"));
		$limit=10;
		$row=$this->picture->select(array("where"=>" title='".$keyword."' AND is_img=1  AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->picture->select(array("where"=>" title like '%".$keyword."%'  AND status<98 AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到您的图片！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
		}
	}
	
	public function caipu($keyword=""){
		$this->loadModel(array("caipu","caipu_data"));
		$limit=10;
		$row=$this->caipu->select(array("where"=>" title='".$keyword."' AND is_img=1  AND status<98 AND userid=".$this->wx['userid']." ","order"=>"id DESC","limit"=>$limit));
		if(empty($row)){
			$row=$this->caipu->select(array("where"=>" title like '%".$keyword."%'  AND status<98 AND is_img=1  AND userid=".$this->wx['userid']."  ","order"=>"id DESC","limit"=>$limit));
		}

		if(!empty($row)){
			//插入解梦日志
			$content="
					 <MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>".count($row)."</ArticleCount>
	<Articles>";
			foreach($row as $r){
			  $content.="<item>
	<Title><![CDATA[".$r['title']."]]></Title> 
	<Description><![CDATA[".$r['description']."]]></Description>
	<PicUrl><![CDATA[".IMAGES_SITE.$r['imgurl'].".middle.jpg"."]]></PicUrl>
	<Url><![CDATA[http://".$_SERVER['HTTP_HOST']."/index.php?m=show&id=".$r['id']."]]></Url>
	</item>";
			}
			$content.="</Articles>";
			return array(
				"error"=>0,
				"content"=>$content
			);
		}else{
			$content="<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[目前找不到您的菜谱！]]></Content>";
			return array(
				"error"=>1,
				"content"=>$content,
			);
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
	
}
?>