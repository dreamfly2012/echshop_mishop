// JavaScript Document
var CLICK="click";
//-------------Xhr

function get(url,fun){
	if(url.indexOf("?")<0){
		url=url+"?ajax=1&rd="+Date.parse( new Date())/1000
	}else{
		url=url+"&ajax=1&rd="+Date.parse( new Date())/1000
	}
	 
	var xhr=null;
	var fun=fun;
	if(xhr){
		return;
	}
	xhr = new window.XMLHttpRequest();
	//xhr=new plus.net.XMLHttpRequest();
	xhr.onreadystatechange=function(){
		switch( xhr.readyState ) {
			case 4:
			
			if ( xhr.status == 200 ) {
				
			 	var data=eval("("+xhr.responseText+")");
			 	fun(data);
			 	xhr=null;
			} else {
				console.log( "xhr请求失败："+xhr.readyState );
			}
			
		}
	};
	xhr.open( "GET", url);
	xhr.send();	 
}

function post(url,data,fun){
	if(url.indexOf("?")<0){
		url+="?ajax=1&rd="+Date.parse( new Date())/1000;
	}else{
		url+="&ajax=1&rd="+Date.parse( new Date())/1000;
	}
	var xhr=null;
	var fun=fun;
	if(fun==undefined){
		fun=data;
		data="";
	}
	if(xhr){
		return;
	}
	xhr = new window.XMLHttpRequest();
	//xhr=new plus.net.XMLHttpRequest();
	
	xhr.onreadystatechange=function(){
		switch( xhr.readyState ) {
			case 4:
			
			if ( xhr.status == 200 ) {
				
			 	var data=eval("("+xhr.responseText+")");
			 	fun(data);
			 	xhr=null;
			} else {
				console.log( "xhr请求失败："+xhr.readyState );
			}
			
		}
	};
	xhr.open( "POST", url);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	 
	xhr.send(json2url(data)); 
}
//End Xhr---------------------------------
function err_user_head(obj,t){
	//event.preventDefault();
	if(t==1){
		obj.src="/static/images/user_head.jpg.small.jpg";
	}else{
		obj.src="/static/images/user_head.jpg.100x100.jpg";
	}
	
}
function err_imgurl(obj,t){
	//event.preventDefault();
	if(t==1){
		obj.src="/static/images/no_image.jpg.small.jpg";
	}else if(t==2){
		obj.src="/static/images/no_image.jpg.middle.jpg";
	}else{
		obj.src="/static/images/no_image.jpg.100x100.jpg";
	}
}
function addClick(url){
	  setTimeout(function(){
		  $.get(url);
	  },5000);
  }
function loginbox(){
	$.get("/index.php?m=login&a=login&ajax=1",function(data){
		showbox("用户登陆",data,400,200);
	});
}


/***店铺加入购物车***/
var shopordercart={};
function shop_orderCart_add(id,ksid,amount){
 
	if(typeof(amount)=="undefined"){
		amount=1;
	}
	
	if(typeof(ksid)=="undefined"){
		ksid=0;
	}
 
	 
	$.get("/index.php?m=shop_order_cart&a=add&object_id="+id+"&ksid="+ksid+"&amount="+amount,function(data){
		if(data.error==0){
			skyToast("成功加入购物车","success");
			
		}else{
			alert(data.message);
		}
	},"json")
}

function shop_orderCart_buy(id,ksid,amount){
	if(typeof(amount)=="undefined"){
		amount=1;
	}
	
	if(typeof(ksid)=="undefined"){
		ksid=0;
	}
	window.location.href="/index.php?m=shop_order_cart&a=buy&object_id="+id+"&ksid="+ksid+"&amount="+amount;
}

 

function shop_orderCart_refresh(){
	get(SITE+"/index.php?m=shop_order_cart&a=getCart",function(data){
		$("#orderCart_inc").html($(data).html());	
	});
	
}

function shop_order_cart_confirm_change(url){
	get(url,function(data){
		if(data.error==1){
			alert(data.message);
		}else{
			window.location.reload();
		}
	});
}

$(document).ready(function(){
	
	$(document).on(CLICK,".shopOrderCartAdd",function(){
		var obj=$(this);
		var amount=$("#amount_"+obj.attr("did")).val();
		shop_orderCart_add(obj.attr("did"),obj.attr("ksid"),amount);
	})
	
	$(document).on(CLICK,".shopOrderCartNum_plus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		get(SITE+"/index.php?m=shop_order_cart&a=num_plus&id="+oid,function(data){
			if(data.error==0){
				 
				var t_o=obj.parents(".shopOrderCart_row").find(".shopOrderCartNum");
				t_o.html(parseInt(t_o.html())+1);
				$(".orderCart_totalmoney").html(data.total_money);
			}else{
				alert(data.message)
			}
		});
	});
	
	$(document).on("click",".shopOrderCartNum_minus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		get(SITE+"/index.php?m=shop_order_cart&a=num_minus&id="+oid,function(data){
			if(data.error==0){
				var t_o=obj.parents(".shopOrderCart_row").find(".shopOrderCartNum");
				t_o.val(parseInt(t_o.val())-1);
				
				$(".orderCard_totalmoney").html(data.total_money);
			}else{
				alert(data.message)
			}
		});
	});
	
	 
	
	$(document).on(CLICK,".shopOrderCart_delete",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		if(confirm("确定删除吗?")){
			get(url,function(data){
				if(data.error==1){
					alert(data.message);
				}else{
					obj.parents(".shopOrderCart_row").remove();
				}
			});
			
		}
	});
	
});

/******End 店铺购物车处理***********/
/***金币换购***/
function exchange(id){
	var o_v=$(".orderCart_attr_value");
	var len=o_v.length;
	var att="";
	if(typeof(amount)=="undefined"){
		amount=1;
	}
	for(var i=0;i<len;i++){
		if(o_v.eq(i).val()==''){
			alert("请选择"+o_v.eq(i).attr("attr_name"));
			return false;
		}
		att +="&"+o_v.eq(i).attr("name")+"="+o_v.eq(i).val();
	}
	$.get("/index.php?m=goldorder&a=check&ajax=1&id="+id+"&attr="+att,function(data){
		if(data.error==0){
			window.location.href=data.url;
		}else{
			alert(data.message);
		}
	},"json")
}
/***金币抢拍***/
function pai(id){
	$.get("/index.php?m=pai&a=check&ajax=1&id="+id,function(data){
		if(data.error==0){
			window.location.href=data.url;
		}else{
			alert(data.message);
		}
	},"json")
}

/****团购商品*****/

function groupbuy(id){
	$.get("/index.php?m=groupbuy&a=check&ajax=1&id="+id,function(data){
		if(data.error==0){
			window.location.href=data.url;
		}else{
			alert(data.message);
		}
	},"json")
}

/*趣购商品*/

function qugou_order(qugou_id,item_id){
	var obj="qugou_status_"+qugou_id;
	var amount=$("body").find("#qugou_amount").val();
	$.get("/index.php?m=qugou_order&a=order&ajax=1&qugou_id="+qugou_id+"&item_id="+item_id+"&amount="+amount+"&r="+new Date().getTime(),function(data){
		if(data.nologin!=undefined){
			loginbox();
		}else if(data.error==0){
			 w=parseInt(data.has_num)/parseInt(data.painum)*100+"%";
			skyToast(data.message,"success");
			$("#"+obj).find(".left_num").html(data.left_num);
			$("#"+obj).find(".has_num").html(data.has_num);
			
			$("#"+obj).find(".active").css("width",w);
			 
			if(parseInt(data.left_num)==0){
				setTimeout(function(){
				window.location="/index.php?m=show&id="+qugou_id+"&itemid="+item_id;
				},1000);
			}
		}else if(data.error==2){
			skyAlert(data.message,"充值提醒","/index.php?m=recharge","javascript:;");
		}else if(data.error==10){
			skyToast(data.message);
			window.location.reload()
		}else{
			skyToast(data.message);
			
		}
	},"json")
}

$(function(){
	
	$(document).on("click","#cat-list-toggle .item h3",function(){
		 if($(this).parents(".item").hasClass("current")){
			 $("#cat-list-toggle .item").removeClass("current");
		 }else{
		 	$("#cat-list-toggle .item").removeClass("current");
			$(this).parents(".item").addClass("current");
		 }
		 
	});
	
	/*********城市区域Ajax*********/
	$(document).on("change","#ajax_province_id",function(){
		if($("#ajax_city_id").length>0){
			$.get("/index.php?m=ajax&a=district&id="+$(this).val(),function(data){
				$("#ajax_city_id").empty().append(data);
				if($("#ajax_town_id").length>0){
					$("#ajax_town_id").empty().append("<option value=0>请选择</option>");
				}
			});
		}
	});
	
	$(document).on("change","#ajax_city_id",function(){
		if($("#ajax_town_id").length>0){
			$.get("/index.php?m=ajax&a=district&id="+$(this).val(),function(data){
				$("#ajax_town_id").empty().append(data);	 
			});
		}
	});
	/*********城市区域Ajax*结束********/
	$(document).on("click",".love-remove-btn",function(){
		var obj=$(this);
		$.get($(this).attr("removeurl"),function(data){
			obj.removeClass("love-remove-btn").addClass("love-add-btn").html("点击收藏");
		},"json");
	});
	
	$(document).on("click",".love-add-btn",function(){
		var obj=$(this);
		$.get($(this).attr("addurl"),function(data){
			obj.removeClass("love-add-btn").addClass("love-remove-btn").html("已收藏");
		},"json");
	});
	
	$(document).on("click",".delete",function(){
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
	
});
$(function(){
	$(document).on("click",".cat_m1",function(){	
		if($(this).next(".cat_m2").css("display")=="none"){
			$(this).parents("#cate").find(".cat_m2").hide();
			$(this).next(".cat_m2").show();
		}else{
			$(this).parents("#cate").find(".cat_m2").hide();
		}
		
	});
	
	$(document).on("change","#group_cat_select",function(data){
		var catid=$(this).val();
		var cc=$(this);
		$.get("/index.php?m=group&a=cat_child&id="+catid,function(data){
			var len=data.length;
			var option="<option value='0'>请选择</option>";
			if(len){
				
				for(var i=0;i<len;i++){
					option=option+'<option value="'+data[i].catid+'">'+data[i].cname+'</option>';
				}
			}
			$("#group_cat_select2").empty().append(option);
		},"json")
	});
	
	$(document).on("click",".ajax_yes",function()
	{
		
		$.get($(this).attr("url"));
		$(this).attr("src","/static/images/yes.gif");
		url=$(this).attr("url");
		$(this).attr("url",$(this).attr("rurl"));
		$(this).attr("rurl",url);
		$(this).removeClass("ajax_yes").addClass("ajax_no");
	});
	
	$(document).on("click",".ajax_no",function()
	{
		$.get($(this).attr("url"));
		$(this).attr("src","/static/images/no.gif");
		url=$(this).attr("url");
		$(this).attr("url",$(this).attr("rurl"));
		$(this).attr("rurl",url);
		$(this).removeClass("ajax_no").addClass("ajax_yes");
	});
	
	/**********关注操作***************/
	$(document).on("click",".ajax_follow",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		$.get(url,function(data){
			if(data.error==0){
				obj.html("关注成功");
			}else{
				alert(data.message);
			}
		},"json");
	});
	
	$(document).on("click",".ajax_unfollow",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		$.get(url,function(data){
			if(data.error==0){
				obj.html("取消成功");
			}else{
				alert(data.message);
			}
		},"json");
		
	});
});