$(function(){
	$(document).on("click","#do_submit",function(){
		$.post("/index.php?m=diary_do&a=save",{title:$("#do_content").val() },function(data){
			if(data.error==0){
				window.location="/index.php?m=diary_do";
			}else{
				skyToast(data.message);
			}
		},"json");
	});
	
	$(document).on("click",".do-post",function(){
		var isdo=0;
		 
		if($(this).attr("checked")==true){
			isdo=1;
		}
		$.post("/index.php?m=diary_do&a=diarydo",{did:$(this).attr("did"),isdo:isdo,day:$(this).attr("day") },function(data){
			
		},"json");
	});
	
	$(document).on("click",".delete_do",function(){
		var obj=$(this);
		if(confirm("确认删除吗")){
			$.get("/index.php?m=diary_do&a=delete&id="+$(this).attr("did"),function(data){
				obj.parents(".item").remove();
			})
		}
	});
	
	$(document).on("click",".diary-delete",function(){
		var obj=$(this);
		if(confirm("确认删除吗")){
			$.get($(this).attr("url"),function(){
				obj.parents(".item").remove();
			});
		}
	});
});