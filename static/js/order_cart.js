/***加入购物车***/
var ordercart={};
function orderCart_add(id,ksid,amount){
 
	if(typeof(amount)=="undefined"){
		amount=1;
	}
	
	if(typeof(ksid)=="undefined"){
		ksid=0;
	}
 
	 
	$.get("/index.php?m=order_cart&a=add&object_id="+id+"&ksid="+ksid+"&amount="+amount,function(data){
		if(data.error==0){
			skyToast("成功加入购物车","success");
			
		}else{
			alert(data.message);
		}
	},"json")
}

function orderCart_buy(id,ksid,amount){
	if(typeof(amount)=="undefined"){
		amount=1;
	}
	
	if(typeof(ksid)=="undefined"){
		ksid=0;
	}
	window.location.href="/index.php?m=order_cart&a=buy&object_id="+id+"&ksid="+ksid+"&amount="+amount;
}

function orderCart_refresh(){
	get(SITE+"/index.php?m=order_cart&a=getCart",function(data){
		$("#orderCart_inc").html($(data).html());	
	});
	
}

function order_cart_confirm_change(url){
	get(url,function(data){
		if(data.error==1){
			alert(data.message);
		}else{
			window.location.reload();
		}
	});
}

$(document).ready(function(){
	
	$(document).on(CLICK,".orderCartAdd",function(){
		var obj=$(this);
		var amount=$("#amount_"+obj.attr("did")).val();
		orderCart_add(obj.attr("did"),obj.attr("ksid"),amount);
	})
	
	$(document).on(CLICK,".orderCartNum_plus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		get(SITE+"/index.php?m=order_cart&a=num_plus&id="+oid,function(data){
			if(data.error==0){
				 
				var t_o=obj.parents(".orderCart_row").find(".orderCartNum");
				t_o.val(parseInt(t_o.val())+1);
				$("#orderCart_totalmoney").html(data.data.total_money);
				$("#orderCart_total_num").html(data.data.total_num);
			}else{
				alert(data.message)
			}
		});
	});
	
	$(document).on("click",".orderCartNum_minus",function(){
		var oid=$(this).attr("oid");
		var obj=$(this);
		get(SITE+"/index.php?m=order_cart&a=num_minus&id="+oid,function(data){
			if(data.error==0){
				var t_o=obj.parents(".orderCart_row").find(".orderCartNum");
				t_o.val(parseInt(t_o.val())-1);
				
				$("#orderCart_totalmoney").html(data.data.total_money);
				$("#orderCart_total_num").html(data.data.total_num);
			}else{
				alert(data.message)
			}
		});
	});
	
	$(document).on("click",".orderCart_attr",function(){
		var obj=$(this).parents(".orderCart_attr_item");
		var aid=obj.attr("attr_id");
		obj.find(".orderCart_attr").removeClass("active");
		$(this).addClass("active");
		$("#orderCart_attr_"+aid).val($(this).attr("val"));
		
	});
	
	$(document).on(CLICK,".cart_delete",function(){
		var url=$(this).attr("url");
		var obj=$(this);
		if(confirm("确定删除吗?")){
			get(url,function(data){
				if(data.error==1){
					alert(data.message);
				}else{
					obj.parents(".orderCart_row").remove();
					$("#orderCart_totalmoney").html(data.data.total_money);
					$("#orderCart_total_num").html(data.data.total_num);
				}
			});
			
		}
	});
	
});

/******End 购物车处理***********/