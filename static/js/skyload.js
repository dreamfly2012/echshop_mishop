/*加载公用js文件*/
var iframedir="../";
var ROOT="../";
var INDEXPAGE="index.html";
var inapp=true; 
var CLICK="tap";
var siteid,ymdian,koudai,shopid;
var rd=Date.parse(new Date())/1000;//随机数
var SITE="http://waimai.deitui.com/";
var IMAGES_SITE=SITE;
var authpara="&authcode="+localStorage.getItem("authcode");
var basejs;
if(inapp){
	basejs=["js/jquery-1.11.3.min.js","js/mui.min.js","js/jquery.flexslider-min.js","js/jquery.Spinner.js","js/template-native.js","js/base64.js","js/common.js","js/app.js"]	;
}else{
	basejs=["js/jquery-1.11.3.min.js","js/jquery.flexslider-min.js","js/jquery.Spinner.js","js/template-native.js","js/base64.js","js/common.js","js/wap.js"]	;
}

var basecss=["css/style.css","css/app.css"];

/*css加载器*/
function loadcss(css){
	for(var i=0;i<css.length;i++){
		addcss(css[i]);
	}
}
function addcss(file){
	var head = document.head || document.getElementsByTagName("head")[0] || document.documentElement;
	var	css=document.createElement('link'); 
	　　css.href=ROOT+file+"?"+rd; 
	　　css.rel='stylesheet'; 
	　　css.type='text/css'; 
	　　head.appendChild(css);
	 
	
}
/**JS加载器**/
function addjs(i,jss,loadjs_init){
	var head = document.head || document.getElementsByTagName("head")[0] || document.documentElement,
	script,options,s;
	var i=i;
	s = options || {};
	var url = ROOT+jss[i]+"?"+rd;
	script = document.createElement("script");
	script.async = false;
	script.type = "text/javascript";
	script.charset="utf-8";
	script.src = url;
	head.insertBefore(script, head.firstChild);
	script.addEventListener("load", function() {
		if(i<jss.length-1){
			addjs(i+1,jss,loadjs_init);
		}else{
					 
			loadjs_init();
		}
	},false);
	 
	
}
function loadjs(jss,loadjs_init){ 
	addjs(0,jss,loadjs_init);	
}

/*END js加载器*/
function skyready(fun){
	if(inapp){
		if(window.plus){ 
			fun();
		}else{
			document.addEventListener('plusready',fun(),false);
		}
	}else{
		$(document).ready(function(){
			fun();
		})
	}
}
