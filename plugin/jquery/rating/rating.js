$(function(){
	raty_html(".raty-row",10);
	function raty_html(obj,len){
		var html='';
		for(var i=0;i<10;i++){
			html =html+'<div  class=" raty-btn bg-star-off"></div>';
		}
		
		$(obj).append(html);
		var j=$(obj).length;
		for(var k=0;k<j;k++){
			var jf=$(obj).eq(k).attr("jf");
			raty_pingfen($(obj).eq(k),jf);
		}
	}
	$(document).on("click mouseover",".raty-btn",function(){		
		var obj=$(this).parents(".raty-row");
		//alert(obj.attr("readonly"));
		if(obj.attr("read")==1) return false;
		var index=$(this).index();
		obj.find(".raty-btn").removeClass("bg-star-on");
		for(var i=0;i<=index;i++){
			obj.find(".raty-btn").eq(i).addClass("bg-star-on");
		}
		$("#"+obj.attr("data")).val(index+1);
		
	});
	
	function raty_pingfen(objstar,jf)
	{
		jf=parseInt(jf);
		
		for(var i=0;i<jf;i++){
			objstar.find(".raty-btn").eq(i).addClass("bg-star-on");
			$("#"+objstar.attr("data")).val(jf);
		}
	}
	
});