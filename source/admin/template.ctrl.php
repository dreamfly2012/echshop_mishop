<?php
class templateControl extends skymvc{
	
	public function __construct(){
		parent::__construct();
		$this->loadModel("sites");
	}
	
	public function onDefault(){
		$site=$this->sites->selectRow("");
		$dir=ROOT_PATH."themes/";
		$hd=opendir($dir);
		$arr=array();
		$i=0;
		while(($f=readdir($hd))!=false)
		{
			if($f!="." && $f!=".."  && $f!='admin' && !is_file($dir.$f)  )
			{
				$arr[$i]['skins']=$f;
				if(file_exists($dir.$f."/index/config.php")){
					require_once("{$dir}{$f}/index/config.php");
					$arr[$i]['skinsimg']="themes/".$f."/index/skins.jpg";
					$arr[$i]['skinsdir']=$f;
					$arr[$i]['skinsname']=$skinsname;
					$arr[$i]['skinsauthor']=$skinsauthor;
					$arr[$i]['skinsversion']=$skinsversion;
					$arr[$i]['skinsauthorsite']=$skinsauthorsite;
					$arr[$i]['skinstype']=$skinstype;
					$arr[$i]['skinsprice']=$skinsprice;
					unset($skinsprice);
					if(SKINS==$f or WAPSKINS==$f )
					{
						$arr[$i]['using']="<font color='red'>正在使用</a>";					
					}else
					{	
						if($skinstype=='wap'){
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&wapskins=1&skins={$f}'>使用</a>";
						}else{
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&skins={$f}'>使用</a>";
						}
					}
					$i++;
				}
				
				if(file_exists($dir.$f."/wap/config.php")){
					require_once("{$dir}{$f}/wap/config.php");
					$arr[$i]['skinsimg']="themes/".$f."/wap/skins.jpg";
					$arr[$i]['skinsdir']=$f;
					$arr[$i]['skinsname']=$skinsname;
					$arr[$i]['skinsauthor']=$skinsauthor;
					$arr[$i]['skinsversion']=$skinsversion;
					$arr[$i]['skinsauthorsite']=$skinsauthorsite;
					$arr[$i]['skinstype']=$skinstype;
					$arr[$i]['skinsprice']=$skinsprice;
					unset($skinsprice);
					if(SKINS==$f or WAPSKINS==$f )
					{
						$arr[$i]['using']="<font color='red'>正在使用</a>";					
					}else
					{	
						if($skinstype=='wap'){
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&wapskins=1&skins={$f}'>使用</a>";
						}else{
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&skins={$f}'>使用</a>";
						}
					}
					$i++;
				}
				
				if(file_exists($dir.$f."/"."config.php"))
				{
					require_once("{$dir}{$f}/config.php");
					$arr[$i]['skinsimg']="themes/".$f."/skins.jpg";
					$arr[$i]['skinsdir']=$f;
					$arr[$i]['skinsname']=$skinsname;
					$arr[$i]['skinsauthor']=$skinsauthor;
					$arr[$i]['skinsversion']=$skinsversion;
					$arr[$i]['skinsauthorsite']=$skinsauthorsite;
					$arr[$i]['skinstype']=$skinstype;
					$arr[$i]['skinsprice']=$skinsprice;
					unset($skinsprice);
					if(SKINS==$f or WAPSKINS==$f )
					{
						$arr[$i]['using']="<font color='red'>正在使用</a>";					
					}else
					{	
						if($skinstype=='wap'){
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&wapskins=1&skins={$f}'>使用</a>";
						}else{
							$arr[$i]['using']="<a href='".APPADMIN."?m=template&a=using&skins={$f}'>使用</a>";
						}
					}
					$i++;
				}
			}
			
		}
		$this->smarty->assign("skinslist",$arr);
		$this->smarty->display("template/index.html");
	}
	
	public function onOnline(){
		
		$this->smarty->display("template/online.html");
	}
	
	public function onUsing(){
		$skins=get('skins','h');
		$f=file_get_contents(ROOT_PATH."config/const.php");
		if(get('wapskins')){
			$f=preg_replace("/define\(\"WAPSKINS\".*\);/iUs","define(\"WAPSKINS\",'".$skins."');",$f,1);
		}else{
			$f=preg_replace("/define\(\"SKINS\".*\);/iUs","define(\"SKINS\",'".$skins."');",$f,1);
		}
		//$f=preg_replace("/skins=\'.*\';/iUs","skins='".$skins."';",$f);
		file_put_contents(ROOT_PATH."config/const.php",$f);
		$this->goall("模板切换成功");
	}
	
	public function onSave(){
		
	}
	
	
	
	
}
?>