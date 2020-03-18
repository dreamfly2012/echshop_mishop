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
	$(".orderCartNum_plus").on("click",function(){
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
	
	$(".orderCartNum_minus").on("click",function(){
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
	
	$(".orderCart_attr").on("click",function(){
		var obj=$(this).parents(".orderCart_attr_item");
		var aid=obj.attr("attr_id");
		obj.find(".orderCart_attr").removeClass("btn-success");
		$(this).addClass("btn-success");
		$("#orderCart_attr_"+aid).val($(this).attr("val"));
		
	});
	
	$(".cart_delete").on("click",function(){
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