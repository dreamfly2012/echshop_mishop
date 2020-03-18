function wbupload(objid,tablename,object_id,success){
	var tablename=tablename,object_id=object_id;
	var $ = jQuery,
        $list = $('#'+objid+' .webuploader-list'),
        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,

        // 缩略图大小
        thumbnailWidth = 100 * ratio,
        thumbnailHeight = 100 * ratio,

        // Web Uploader实例
        uploader;

    // 初始化Web Uploader
    uploader = WebUploader.create({

        // 自动上传。
        auto: true,

        // swf文件路径
        swf:   '/plugin/webuploader/Uploader.swf',

        // 文件接收服务端。
        server: '/index.php?m=upload&a=uploadtao&dir='+tablename+'&siteid=1&new=1&id='+object_id,
		fileVal:'upimg',
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#'+objid+" .webuploader-select",

        // 只允许选择文件，可选。
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });

    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {
        var $li = $(
                '<div id="' + file.id + '" class="file-item thumbnail">' +
                    '<img>' +
                    '<div class="info">' + file.name + '</div>' +
                '</div>'
                ),
            $img = $li.find('img');

        $list.append( $li );

        // 创建缩略图
        uploader.makeThumb( file, function( error, src ) {
            if ( error ) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }

            $img.attr( 'src', src );
        }, thumbnailWidth, thumbnailHeight );
    });

    // 文件上传过程中创建进度条实时显示。
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var $li = $( '#'+file.id ),
            $percent = $li.find('.progress span');

        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<p class="progress"><span></span></p>')
                    .appendTo( $li )
                    .find('span');
        }

        $percent.css( 'width', percentage * 100 + '%' );
    });

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on( 'uploadSuccess', function( file,res ) {
		if(!res.error){
		$.post("/index.php?m=imgs&a=save",{imgurl:res.imgurl,title:'',object_id:object_id,tablename:tablename},function(data){
			 success();
		});
		}
        //$( '#'+file.id ).addClass('upload-state-done');
		
    });

    // 文件上传失败，现实上传出错。
    uploader.on( 'uploadError', function( file ) {
        var $li = $( '#'+file.id ),
            $error = $li.find('div.error');

        // 避免重复创建
        if ( !$error.length ) {
            $error = $('<div class="error"></div>').appendTo( $li );
        }

        $error.text('上传失败');
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on( 'uploadComplete', function( file ) {
		 $( '#'+file.id ).remove();
        //$( '#'+file.id ).find('.progress').remove();
		
    });
}


function modImgsList(objid,cover){
	if(typeof(cover)=='undefined') cover=0;
	var objid=objid;
	var url=$("#"+objid).attr("url")+"&a=list";
	$.get(url,function(data){
		var html='';
		for(var i=0;i<data.length;i++){
			if(data[i].title==''){
				data[i].title='';
			}
			html+='<div class="item" url="'+url+'"><input type="hidden" class="ks-ids" name="ids[]" value="'+data[i].id+'"><div class="img"><img src="'+data[i].imgurl+'.100x100.jpg" ></div>'
			+'<div class="tools"> <span class="ks-left"></span> <span class="ks-right"></span>'
			+(cover?'<span class="ks-cover" src="'+data[i].imgurl+'.100x100.jpg"></span>':'')
			+'<span class="ksclose" did="'+data[i].id+'"></span></div>'
			+' <div class="title "><input class="ks-title"  type="text"   value="'+data[i].title+'" placeholder="如：黑色" did="'+data[i].id+'"> </div></div>';
		}
		$("#"+objid+" .ks-list").html(html);
	},"json")
}


$(function(){
	$(document).on("focusout",".webuploader .ks-title",function(){
		var url=$(this).parents(".webuploader").attr("url");
		if($(this).val()=='' ){
			skyToast('请填写完整');
			return false;
		}
		$.post(url+"&a=save",{title:$(this).val(),id:$(this).attr("did")},function(data){
			 skyToast('保存成功');
		},"json");
	});
	
	$(document).on("click",".webuploader .ksclose",function(){
		var url=$(this).parents(".webuploader").attr("url");
		var obj=$(this);
		$.get(url+"&a=delete&id="+$(this).attr("did"),function(data){
			obj.parents(".item").remove();
		})
	});
	
	$(document).on("click",".webuploader .ks-left",function(){
		var url=$(this).parents(".webuploader").attr("url");
		var p=$(this).parents(".item");
		p.insertBefore(p.prev());
		$.get(url+"&a=orderindex",$(this).parents(".webuploader").find(".ks-list .ks-ids").serialize(),function(){});
	});
	
	$(document).on("click",".webuploader .ks-right",function(){
		var url=$(this).parents(".webuploader").attr("url");
		var p=$(this).parents(".item");
		p.insertAfter(p.next());
		$.get(url+"&a=orderindex",$(this).parents(".webuploader").find(".ks-list .ks-ids").serialize(),function(){});
	});
	
	$(document).on("click",".webuploader .ks-cover",function(){	
		$("#imgurl").val($(this).attr("src"));
		$("#imgShow").html("<img src='"+$(this).attr("src")+".100x100.jpg' width='100'>");
	})
});