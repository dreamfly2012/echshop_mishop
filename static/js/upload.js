function uploadimg(objid)
 {
	 var obj=$("#"+objid);
	 var url=obj.attr("url");
	 if(obj.find(".uploading").length==0){
		obj.append('<div class="uploading">正在上传中...</div>'); 
	 }
	 var fileid=obj.find("input[name=upimg]").attr("id");
	  obj.find(".uploading")
        .ajaxStart(function(){
            $(this).show();
        })
        .ajaxComplete(function(){
            $(this).hide();
        });
        $.ajaxFileUpload
        (
            {
                url:url+'&t='+Math.random(), 
                secureuri:false,
                fileElementId: fileid,
                dataType: 'json',
                success: function (data, status)
                {
                    if(typeof(data.error) != 'undefined')
                    {
                        if(data.error != '')
                        {
                           skyToast(data.error);
                        }else
                        {
							if(obj.find(".imgShow").length>0){
                            	obj.find(".imgShow").html("<img src='"+data.trueimgurl+".100x100.jpg' width='100'>");
							}
							if(obj.find(".imgurl").length>0){
								obj.find(".imgurl").val(data.imgurl);
							}
                        }
                    }else{
						skyToast('未知错误2');
					}
                },
                error: function (data, status, e)
                {
					skyToast('未知错误')
  
                }
            }
        )
        
        return false;
}

