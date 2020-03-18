function gourl(url){
	if(inapp){
		mui.webviews=[];
		mui.openWindow({
			id: url,
			url: url,
			styles:{zindex:999}
		});
	}else{
		window.location=url;
	}
	
}

function goBack(goindex,wbid){
	if(typeof(goindex)=="undefined") goindex="-1";
	if(inapp){
		if(goindex==-2){
			var wobj = plus.webview.currentWebview();
			var opener = wobj.opener();
			opener.hide();
			wobj.hide(); 
		}else{
			mui.back();
		}
	}else{
		if (document.referrer != null && document.referrer != "") {        
	         window.history.go(goindex);
	     } else{
	     		window.history.go(goindex);
	     		setTimeout(function(){
	     			window.location=ROOT+INDEXPAGE;
	     		},300)
				
		}
	}
	
}

//-------------Xhr
var oc_ssid;
function get(url,data,fun){
	if(url.indexOf("?")<0){
		url=url+"?ajax=1&CORS=1"+authpara+"&rd="+Date.parse(new Date())/1000
	}else{
		url=url+"&ajax=1&CORS=1"+authpara+"&rd="+Date.parse(new Date())/1000
	}
	if(oc_ssid){
		url=url+"&oc_ssid="+oc_ssid;
	}
	 
	var fun=fun;
	if(fun==undefined){
		fun=data;
		$.get(url,function(data){
			fun(data);
		},"json");
	}else{
		$.get(url,data,function(data){
			fun(data);
		},"json");
	}
	 
	
	 
}

function post(url,data,fun){
	if(url.indexOf("?")<0){
		url+="?ajax=1&CORS=1"+authpara+"&rd="+Date.parse(new Date())/1000;
	}else{
		url+="&ajax=1&CORS=1"+authpara+"&rd="+Date.parse(new Date())/1000;
	}
	if(oc_ssid){
		url=url+"&oc_ssid="+oc_ssid;
	}
	var xhr=null;
	var fun=fun;
	if(fun==undefined){
		fun=data;
		$.post(url,function(data){
			fun(data);
		},"json");
	}else{
		$.post(url,data,function(data){
			fun(data);
		},"json");
	}
	
}
//End Xhr--------------------------------- 
 
function get_oc_ssid(){
	oc_ssid=localStorage.getItem("oc_ssid");
	if(!oc_ssid){
		get(SITE+"/index.php?m=login&a=get_oc_ssid",function(data){
			oc_ssid=data.message;
			localStorage.setItem("oc_ssid",oc_ssid);
		})
		
	}
	return oc_ssid;
}
get_oc_ssid();
/**无限加载*/
var listload={
	loading:false,
	loadid:"#loading",
	loadHeight:160,/*加载离底部高度*/
	loadtime:1000,/*两次加载时间*/
	loadend:false,
	showload:function(loadsuccess){
		
		$(window).scroll(function(){
			if ($(document).height() - $(this).scrollTop() - $(this).height()<listload.loadHeight) {
				listload.success(loadsuccess);
			}
		  });
		$(document).on(CLICK,listload.loadid,function(){
			listload.success(loadsuccess);
		});
	},
	success:function(loadsuccess){
		if(listload.loadend){
			$(loadid).hide();
			return false;
		}
		var loadid=listload.loadid;
				$(loadid).show();
				$(loadid).addClass("active");		
				var ldtimer=setTimeout(function(){
					$(loadid).removeClass("active");
					listload.loading=false;
					clearTimeout(ldtimer);
				},listload.loadtime);
				if(listload.loading==false){
					loadsuccess();
				}
				listload.loading=true;
	},
	hideload:function(){
		var loadid=listload.loadid;
		$(loadid).removeClass("active");
		listload.loading=false;
	}
};

function imgLazy(o){
	if(typeof(o)=='undefined') {
		var o='body';
	}
	setTimeout(function(){
		var obj=$(o).find(".lazy");
		var len=obj.length;
		for(var i=0;i<len;i++){
			var a=obj.eq(i);
			if(a.attr("url")!=a.attr("src")){
				a.attr("src",a.attr("url"));
			}
			
		}
	},200);
}

 

function json2url(data){
	var str="";
 	   for(name in data){
		   if(data[name] instanceof Object){
			   obs=data[name];
			   for( b in obs){
				   
				   if(obs[b] instanceof Object){
					   oc=obs[b];
					   for(c in oc){
						   str +="&"+name+"['"+b+"']['"+c+"']="+oc[c];
					   }
				   }else{
					   str +="&"+name+"['"+b+"']="+obs[b];
				   }
			   }
		   }else{
			   str +="&"+name+"="+data[name];
		   }
	   }
	   return str;
}



function getPara() {
		var data= new Array();
        var url = window.location.href;  //获取网址字符串      
        var len = url.length;
        var offset = url.indexOf("?");
        var str = url.substr(offset+1, len);     
        var args = str.split("&");      
        len = args.length; 
        
        for (var i = 0; i < len; i++) {
            str = args[i];
            var arg = str.split("=");            
            if (arg.length <= 1) break;
            else {
            	
                data[arg[0]]=arg[1];
                
            }
        }
       
        return data;
 }



 

function goLogin(){
	gourl(ROOT+'user/index.html')
}

function checkLogin(suc){
	
}



/*************Base****************/
function err_user_head(obj,t){
	//event.preventDefault();
	if(t==1){
		obj.src=ROOT+"/img/user_head.jpg.small.jpg";
	}else{
		obj.src=ROOT+"/img/user_head.jpg.100x100.jpg";
	}
	
}

function err_imgurl(obj,t){
	//event.preventDefault();
	if(t==1){
		obj.src=ROOT+"/img/no_image.jpg.small.jpg";
	}else if(t==2){
		obj.src=ROOT+"/img/no_image.jpg.middle.jpg";
	}else{
		obj.src=ROOT+"/img/no_image.jpg.100x100.jpg";
	}
}
function botnav_cart_num(type){
	get(SITE+"/index.php?m=ajax&a=botnav_cart_num",function(data){
		if(data>0){
			$("#botnav_carnum").show().html(data);
		}else{
			$("#botnav_carnum").hide();
		}
		setTimeout(function(){
			botnav_cart_num();
		},30000);
	});
	
}
 
/*图片缩略*/
function imgResize(w,h){
	 //var w=240,h=160;
	var iw=$(".imgResize:eq(0)").width();
	var ih=h*iw/(2*w);
	$(".imgResize").css({height:ih,overflow:"hidden"});
 }

function copyright(){
	var html='<footer style="width:100%; clear:both"><div class="copyright">copyright &copy; 2015 得推网络科技</div></footer>';
	$("body").append(html);
}
$(function(){
	$(document).on(CLICK,".delete",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		if(confirm("删除后不可恢复，确定删除吗?")){
			$.get(url,function(data){
				if(data.error==1){
					alert(data.message);
				}else{
					obj.parents("tr").remove();
				}
			},"json");
			
		}
	});
	
		$(document).on(CLICK,".mui-action-back",function(){
		goBack();
	})
});

getSite();
getYmdian();
getKoudai();
var $get=getPara();
/**********************************/
