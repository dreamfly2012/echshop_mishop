!function ($) {

  "use strict"; // jshint ;_;


 /* TAB CLASS DEFINITION
  * ==================== */

  var Tab = function (element) {
    this.element = $(element)
  }

  Tab.prototype = {

    constructor: Tab

  , show: function () {
      var $this = this.element
        , $ul = $this.closest('ul:not(.dropdown-menu)')
        , selector = $this.attr('data-target')
        , previous
        , $target
        , e

      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }

      if ( $this.parent('li').hasClass('active') ) return

      previous = $ul.find('.active:last a')[0]

      e = $.Event('show', {
        relatedTarget: previous
      })

      $this.trigger(e)

      if (e.isDefaultPrevented()) return

      $target = $(selector)

      this.activate($this.parent('li'), $ul)
      this.activate($target, $target.parent(), function () {
        $this.trigger({
          type: 'shown'
        , relatedTarget: previous
        })
      })
    }

  , activate: function ( element, container, callback) {
      var $active = container.find('> .active')
        , transition = callback
            && $.support.transition
            && $active.hasClass('fade')

      function next() {
        $active
          .removeClass('active')
          .find('> .dropdown-menu > .active')
          .removeClass('active')

        element.addClass('active')

        if (transition) {
          element[0].offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }

        if ( element.parent('.dropdown-menu') ) {
          element.closest('li.dropdown').addClass('active')
        }

        callback && callback()
      }

      transition ?
        $active.one($.support.transition.end, next) :
        next()

      $active.removeClass('in')
    }
  }


 /* TAB PLUGIN DEFINITION
  * ===================== */

  var old = $.fn.tab

  $.fn.tab = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tab')
      if (!data) $this.data('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tab.Constructor = Tab


 /* TAB NO CONFLICT
  * =============== */

  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


 /* TAB DATA-API
  * ============ */

  $(document).on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
    e.preventDefault()
    $(this).tab('show')
  })

}(window.jQuery);
//ENd Tabs
$(document).ready(function(){
	//删除
	$(".del,.delete").click(function(){
		var obj=$(this);
		if(confirm("删除后不可恢复，确认删除吗?")){
			$.get($(this).attr("url"),function(data){
				if(data.error=='0'){
					obj.parents("tr").remove();
				}else{
					alert(data.message);
				}
			},"json");
			
		}
	});
	
	$(document).on("click",".ajax_yes",function()
	{
		
		$.get($(this).attr("url"));
		$(this).attr("src","static/images/yes.gif");
		var url=$(this).attr("url");
		$(this).attr("url",$(this).attr("rurl"));
		$(this).attr("rurl",url);
		$(this).removeClass("ajax_yes").addClass("ajax_no");
	});
	
	$(".ajax_no").live("click",function()
	{
		$.get($(this).attr("url"));
		$(this).attr("src","static/images/no.gif");
		var url=$(this).attr("url");
		$(this).attr("url",$(this).attr("rurl"));
		$(this).attr("rurl",url);
		$(this).removeClass("ajax_no").addClass("ajax_yes");
	});
	
	//表单失去焦点更新数据
	$(".blur_update").blur(function(){
		var data=$(this).val();
		var obj=$(this);
		$.get($(this).attr("url"),{data:data},function(data){
			obj.after("<img id='blur_update_tip' src='/static/images/yes.gif'>");
			setTimeout(function(){$("#blur_update_tip").remove()},"1000");
		});
	});
	$("#ajax_catid").live("change",function(){
		var pid=$(this).val();
		if(pid!="0"){
			$.get($(this).attr("url"),{pid:$(this).val()},function(data){
				$("#ajax_catid_2nd").empty().append(data).show();					 
			});
		}else{
			$("#ajax_catid_2nd").empty().hide();
			if($("#ajax_catid_3nd")){$("#ajax_catid_3nd").hide().empty();}
		}
	})
	
	$("#ajax_catid_2nd").live("change",function(){
		var pid=$(this).val();
		if(pid!="0"){
			$.get($(this).attr("url"),{pid:pid},function(data){
				$("#ajax_catid_3nd").empty().append(data).show();
			});
		}else{
			$("#ajax_catid_3nd").hide().empty();
		}
	})
	
	//$("tr:nth-child(1)").addClass('first'); 
	//$('tr').addClass('success'); 
	$('tr:odd').addClass('info'); //奇偶变色，添加样式 
	
	
});