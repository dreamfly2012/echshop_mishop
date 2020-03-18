// JavaScript Document
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

function loginbox(){
	$.get("/index.php?m=login&a=login&ajax=1",function(data){
		showbox("用户登陆",data,300,200);
	});
}
/***加入购物车***/
var ordercart={};
function orderCart_add(id,type_id,amount){
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
	 
	$.get("/index.php?m=order_cart&a=add&object_id="+id+"&type_id="+type_id+"&amount="+amount+att,function(data){
		if(data.error==0){
			skyToast("成功加入购物车","success");
			
		}else{
			alert(data.message);
		}
	},"json")
}

function orderCart_buy(id,type_id,amount){
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
	window.location.href="/index.php?m=order_cart&a=buy&object_id="+id+"&type_id="+type_id+"&amount="+amount+att;
}

function orderCart_refresh(){
	$.get("/index.php?m=order_cart&a=getCart",function(data){
		$("#orderCart_inc").html($(data).html());	
	});
	
}

function order_cart_confirm_change(url){
	$.get(url,function(data){
		if(data.error==1){
			alert(data.message);
		}else{
			window.location.reload();
		}
	},"json");
}

$(document).ready(function(){
	$(document).on("click",".orderCartNum_plus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		$.get("/index.php?m=order_cart&a=num_plus&id="+oid,function(data){
			if(data.error==0){
				 
				var t_o=obj.parents(".orderCart_row").find(".orderCartNum");
				t_o.html(parseInt(t_o.html())+1);
				$(".orderCard_totalmoney").html(data.total_money);
			}else{
				alert(data.message)
			}
		},"json");
	});
	
	$(document).on("click",".orderCartNum_minus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		$.get("/index.php?m=order_cart&a=num_minus&id="+oid,function(data){
			if(data.error==0){
				var t_o=obj.parents(".orderCart_row").find(".orderCartNum");
				if(t_o.html()){
					t_o.html(parseInt(t_o.html())-1);
				}else{
					t_o.val(parseInt(t_o.val())-1);
				}
				
				$(".orderCard_totalmoney").html(data.total_money);
			}else{
				alert(data.message)
			}
		},"json");
	});
	
	$(document).on("click",".orderCart_attr",function(){
		var obj=$(this).parents(".orderCart_attr_item");
		var aid=obj.attr("attr_id");
		obj.find(".orderCart_attr").removeClass("btn-success");
		$(this).addClass("btn-success");
		$("#orderCart_attr_"+aid).val($(this).attr("val"));
		
	});
	
	$(document).on("click",".cart_delete",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		if(confirm("确定删除吗?")){
			$.get(url,function(data){
				if(data.error==1){
					alert(data.message);
				}else{
					obj.parents(".orderCart_row").remove();
					$(".orderCart_pronum").html(parseInt($(".orderCart_pronum").html())-1); 
					$(".orderCard_totalmoney").html(data.total_money);
				}
			},"json");
			
		}
	});
	
});

/******End 购物车处理***********/
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
					if(obj.parents("tr").length>0){
						obj.parents("tr").remove();
					}
					
					if(obj.parents(".item").length>0){
						obj.parents(".item").remove();
					}
				}
			},"json");
			
		}
	});
	
});
$(function(){
	 
	
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
	
	if($("#goTop").length==0){
		$("body").append('<a href="#" class="gotop "><i class="iconfont icon-gotop" style="font-size:32px;"></i></a>');
	}
	
	
	
});