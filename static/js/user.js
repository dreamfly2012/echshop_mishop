// JavaScript Document

//Login
function loginBox(){
	var html='<div id="a_login" class="a_login"><div class="pd">'
		+'<form id="a_loginform" >'
        	+'<div class="item"><input type="text" class="input" name="username"  placeholder="用户名/手机/邮箱"></div>'
            +'<div class="item"><input type="password" class="input" name="password" placeholder="密码"></div>'
            +'<div class="item"><input type="text" class="input input-checkcode" name="checkcode"> <img src="/index.php?m=checkcode"  class="input-img"  onClick="this.src=\'/index.php?m=checkcode&r=\'+new Date()"></div>'
            +'<div class="item_btns">'
            +	'<div class="btn btn-success" onClick="loginSubmit()" >登录</div>'
             +  ' <div class="btn btn-primary" onClick="regBox()">注册</div>'
            +' </div>'
        +'</form>'
	+'</div></div>';
	showbox("登录",html,"260","200");
	 	
}

function loginSubmit(a_login_refresh){
	$.post("/index.php?m=login&a=loginsave&ajax=1",$("#a_loginform").serialize(),function(data){
			if(data.error==0){
				skyToast('登录成功',"success");
				showboxClose();
				if(a_login_refresh==1){
					window.location.reload();
				}
			}else{
				skyToast(data.message);
			}
		},"json")
}

function regBox(){
	var html='<div id="a_reg">'
		+'<form id="a_regform" class="a_login"><div class="pd">'
        +'	<div class="item"><input type="text" class="input" name="telephone"  placeholder="手机"></div>'
        + '   <div class="item"><input type="text" class="input" name="username"  placeholder="用户名"></div>'
        +'    <div class="item"><input type="text" class="input" name="email"  placeholder="邮箱"></div>'
        +'    <div class="item"><input type="password" class="input" name="password" placeholder="密码"></div>'
        +'    <div class="item"><input type="password" class="input" name="password2" placeholder="确认密码"></div>'
             
        +'    <div class="item_btns">'
        +'    	<div class="btn btn-success" onClick="regSubmit()" id="a_reg_submit">注册</div>'
        +'        <div class="btn btn-primary" onClick="loginxBox()">登录</div>'
        +'     </div>'
       +' </form>'
	+'</div></div>';
	showbox("注册",html,"260","200");
}

 

function regSubmit(){
	$.post("/index.php?m=register&a=regsave&ajax=1",$("#a_regform").serialize(),function(data){
			if(data.error==0){
				skyToast('注册成功',"success");
				showboxClose();
				
			}else{
				skyToast(data.message);
			}
		},"json")
}

function userLogout(){
	$.get("/index.php?m=login&a=logout&ajax=1",function(data){
		skyToast('注销成功');
		user_tools();	
	},"json");
	
}

function userEditBox(){
	$.get("/index.php?m=user&a=getUser&ajax=1",function(data){
		if(data.nologin){
			loginBox();
		}else{
			var html='<div id="a_userEdit"  class="a_login"><div class="pd">'
			+'<form id="a_userEdit_form" method="post">'
			 
			+'<div class="item"><input class="input" type="text" name="nickname" value="'+data.nickname+'" placeholder="昵称"></div>'
			+' <div class="item" style="height:60px;"><textarea name="info" class="input" style="height:60px;" placeholder="简介">'+data.info+'</textarea> </div>'
			+ ' <div class="item_btns">'
			+' 	<div class="btn btn-success" onclick="userEditSubmit()">保存</div>'
			+'</div>'
			+'  </form>'
			+'</div></div>';
			showbox("用户信息编辑",html,"260","200");
		}
	},"json")
}

function userEditSubmit(){
	 
	$.post("/index.php?m=user&a=save&ajax=1",$("#a_userEdit_form").serialize(),function(data){
			if(data.error==0){
				skyToast('修改成功',"success");
				showboxClose();
			}else{
				skyToast(data.message);
			}
		},"json")
}

function userPwdBox(){
	$.get("/index.php?m=user&a=getUser&ajax=1",function(data){
		if(data.nologin){
			loginBox();
		}else{
			var html='<div id="a_userPwd"  class="a_login"><div class="pd">'
		+'<form id="a_userPwd_form" method="post" >'
    +' <div class="item"><input class="input" type="text" name="oldpassword" value="" placeholder="旧密码"></div>'
   +' <div class="item"><input class="input" type="text" name="password" value="" placeholder="新密码"></div>'
   +' <div class="item"><input class="input" type="text" name="password2" value="" placeholder="重复密码"></div>'
     
   +' <div class="item_btns">'
    +'	<div class="btn btn-success" onclick="userPwdBox()">密码修改</div>'
    +'</div>'
   +' </form>'
+'</div></div>';
			showbox("用户密码修改",html,"260","200");
		}
	},"json")
}

function userPwdSubmit(){
	 
	$.post("/index.php?m=user&a=passwordsave&ajax=1",$("#a_userPwd_form").serialize(),function(data){
			if(data.error==0){
				skyToast('修改成功',"success");
				showboxClose();
			}else{
				skyToast(data.message);
			}
		},"json")
}


function userInfoBox(){
	$.get("/index.php?m=user&a=getUser&ajax=1",function(data){
		if(data.nologin){
			loginBox();
		}else{
			var html='<div id="a_userInfo" class="a_userInfo"><div class="pd">'	
			+  '<div class="item"><span class="l">ID</span><span class="r">'+data.userid+'</span></div>' 
   			+  '<div class="item"><span class="l">昵称</span><span class="r">'+data.nickname+'</span></div>'
   			+ '<div class="item"><span class="l">邮箱</span><span class="r">'+data.email+'</span></div>'
   			+ '<div class="item"><span class="l">手机</span><span class="r">'+data.telephone+'</span></div>'
   			+  '</div></div>';
			showbox("用户信息",html,"260","200");
		}
	},"json")
}


 
function af_province(){
		$.get("/index.php?m=ajax&a=district&id="+$("#a_province").val(),function(data){
			$("#a_city").empty().append(data);		
		});
}
 
	
function af_city(){
		$.get("/index.php?m=ajax&a=district&id="+$("#a_city").val(),function(data){
			$("#a_town").empty().append(data);		
		});	 
}

function userAddrSubmit(){
	$.post("/index.php?m=user&a=addrsave&ajax=1",$("#a_userAddr_form").serialize(),function(data){
			if(data.error==0){
				skyToast('修改成功',"success");
				showboxClose();
			}else{
				skyToast(data.message);
			}
		},"json")
}

function userAddrBox(){
	$.get("/index.php?m=user&a=getAddr&ajax=1",function(data){
		
		if(data.nologin){
			loginBox();
		}else{
			var u=data;
			var user=u.user;
			var pro=city=town="";
			pro='<option value="0">省</option>';
			$.each(u.province,function(key,val){
				pro=pro+'<option value="'+key+'" '+(key==user.reprovince?'selected':'')+'>'+u.province[key]+'</option>';
			});
			$("#a_province").empty().append(pro);
			city='<option value="0">市</option>';
			if(u.city!=null){
					 
				$.each(u.city,function(key,val){
					 
					city=city+'<option value="'+key+'" '+(key==user.recity?'selected':'')+'>'+u.city[key]+'</option>';
				});
				$("#a_city").empty().append(city);
			}
			town='<option value="0">镇</option>';
			if(u.town!=null){
					 
				$.each(u.town,function(key,val){
					town=town+'<option value="'+key+'" '+(key==user.retown?'selected':'')+'>'+u.town[key]+'</option>';
				});
				$("#a_town").empty().append(town);	
			}
			
			var html='<div id="a_userAddr" class="a_login"><div class="pd">'
+'<form id="a_userAddr_form">'
+	' <div class="item"><input type="text" name="retelephone" value="'+user.retelephone+'" placeholder="收件人号码" class="input"></div>'
 +   ' <div class="item"><input type="text" name="renickname" value="'+user.renickname+'" placeholder="收件人名称" class="input"></div>'
  +  ' <div class="item">'
  +  ' <select class="l3" name="reprovince" onchange="af_province()" id="a_province"  >'+pro+'</select>'
 +   ' <select class="l3"  name="recity" onchange="af_city()" id="a_city" >'+city+'</select>'
  +  ' <select class="l3" name="retown" id="a_town">'+town+'</select>'
  +  ' </div>'
  +  '<div class="item"><input type="text" name="readdr" class="input" value="'+user.readdr+'" placeholder="详细地址" /></div>'
  + ' <div class="item_btns"> <div class="btn btn-success" onClick="userAddrSubmit()" id="a_reg_submit">保存地址</div> </div>'
' </form> '   
     
'</div></div>';
			showbox("地址修改",html,"360","200");
			
		}
	},"json");
	
}

 
function user_tools(){
	var m=$("#a_user_tools").length;
	if(m>0){
		if($("#a_user_tools").css("display")=='block'){
			$("#a_user_tools").hide();
			return false;
		}else{
			$("#a_user_tools").show();
			
		}
	}
	$.get("/index.php?m=user&a=getUser&ajax=1",function(data){
		
		
		if(data.nologin){
			 var html='<div class="item" onClick="loginBox()">用户登录</div><div class="item" onClick="regBox()">用户注册</div> '
		}else{
			var html= '<div class="item" onClick="userInfoBox()">用户信息</div>'
			+'<div class="item" onClick="userEditBox()">信息修改</div>'
			+'<div class="item" onClick="userPwdBox()">密码修改</div>'
			+'<div class="item" onClick="userAddrBox()">收货地址</div> '
			+'<div class="item" onClick="userLogout()">退出</div> ';
		}
		
		 
		if(m==1){
			$("#a_user_tools").html(html);
		}else{
			$("body").append('<div id="a_user_tools" class="a_user_tools">'+html+'</div>');
		}
		
	},"json");
}
//EndLogin

 //Fav
 function af_fav_add(obj){
	 var obj=$(obj);
	 $.get($(obj).attr("url"),function(data){
		if(data.nologin){
			loginBox();
		}else if(data.error){
			 skyToast(data.message);
		 }else{
		 	skyToast("收藏成功");
			obj.html('已收藏');
		 }
	 },"json");
 }
 
 function af_fav_del(obj){
	 var obj=$(obj);
	 $.get($(obj).attr("url"),function(data){
		if(data.nologin){
			loginBox();
		}else if(data.error){
			 skyToast(data.message);
		 }else{
		 	obj.parents(".item").remove();
		 }
	 },"json");
 }
 
  function af_love_add(obj){
	 var obj=$(obj);
	 $.get($(obj).attr("url"),function(data){
		if(data.nologin){
			loginBox();
		}else if(data.error){
			 skyToast(data.message);
		 }else{
		 	skyToast("赞成功");
			obj.html('已赞');
		 }
	 },"json");
 }
 
 function af_love_del(obj){
	 var obj=$(obj);
	 $.get($(obj).attr("url"),function(data){
		if(data.nologin){
			loginBox();
		}else if(data.error){
			 skyToast(data.message);
		 }else{
		 	obj.parents(".item").remove();
		 }
	 },"json");
 }

 //End Fav