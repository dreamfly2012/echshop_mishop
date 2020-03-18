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
		showbox("用户登陆",data,400,200);
	});
}

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
	
	$("#cat-list-toggle .item h3").bind("click",function(){
		 if($(this).parents(".item").hasClass("current")){
			 $("#cat-list-toggle .item").removeClass("current");
		 }else{
		 	$("#cat-list-toggle .item").removeClass("current");
			$(this).parents(".item").addClass("current");
		 }
		 
	});
	
	/*********城市区域Ajax*********/
	$("#ajax_province_id").on("change",function(){
		if($("#ajax_city_id").length>0){
			$.get("/index.php?m=ajax&a=district&id="+$(this).val(),function(data){
				$("#ajax_city_id").empty().append(data);
				if($("#ajax_town_id").length>0){
					$("#ajax_town_id").empty().append("<option value=0>请选择</option>");
				}
			});
		}
	});
	
	$("#ajax_city_id").on("change",function(){
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
	
	$(".delete").click(function(){
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
	$(".cat_m1").bind("click",function(){	
		if($(this).next(".cat_m2").css("display")=="none"){
			$(this).parents("#cate").find(".cat_m2").hide();
			$(this).next(".cat_m2").show();
		}else{
			$(this).parents("#cate").find(".cat_m2").hide();
		}
		
	});
	
	$("#group_cat_select").bind("change",function(data){
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
	
	$(".ajax_yes").on("click",function()
	{
		
		$.get($(this).attr("url"));
		$(this).attr("src","/static/images/yes.gif");
		url=$(this).attr("url");
		$(this).attr("url",$(this).attr("rurl"));
		$(this).attr("rurl",url);
		$(this).removeClass("ajax_yes").addClass("ajax_no");
	});
	
	$(".ajax_no").on("click",function()
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