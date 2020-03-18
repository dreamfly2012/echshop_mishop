

function shareH5Show(){
	var  html='<div id="sharemenu-box">'
	+'<div id="sharepopover" class="sharepopover"></div>'
	+'<div id="sharemenu" class="sharemenu">'
	+'<div class="bdsharebuttonbox" data-tag="share_1">'	 
	+'<div class="item"><a class="bds bds_qzone" data-cmd="qzone" href="#"></a></div>'
	+'<div class="item"><a class="bds bds_tsina" data-cmd="tsina"></a></div>'
	+'<div class="item"><a class="bds bds_tqq" data-cmd="tqq"></a></div>'
	+'		<div class="item" onclick="shareHide()"><i class="bds iconfont icon-close"></i></div>'
	+'</div>'
	+'</div>'
	+'</div>';
	if($("#sharemenu-box").length==0){
		$("body").append(html);
		 window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"24"},"share":{},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)]; 

	}
	$("#sharemenu-box").show();
	document.querySelector("#sharepopover").style.display="block";
	document.querySelector("#sharemenu").style.display="block";
	setTimeout(function(){
		document.querySelector("#sharepopover").style.opacity="1";
		document.querySelector("#sharemenu").style.opacity="1";
		document.querySelector("#sharemenu").style.webkitTransform="translateY(0)";
	},10);
}


function shareHide(){
	$("#sharemenu-box").hide();
}

$(function(){
	$("#share-btn").on("click",function(){
		shareH5Show();
	});
	
	$(document).on("click","#sharepopover",function(){
		$("#sharemenu-box").hide();
	});
	
});
