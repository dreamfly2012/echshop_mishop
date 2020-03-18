<?php
$config['product_attrtpl']='
    {if $attr} <!--产品属性-->
  <div class="attr-filter">
  {foreach item=c from=$attr}
 
      {if $c.attr_type neq 1}        
     
       
      	<div class="orderCart_attr_item row" style="margin-bottom:10px;" attr_id={$c.id}>
		<input type="hidden" class="orderCart_attr_value" name="attr[{$c.id}]" attr_name="{$c.title}" id="orderCart_attr_{$c.id}" />
       		<div class="g-sd1 hd">{$c.title}</div>
			<div class="g-mn1"> 
				<div class="g-mn1c">  
            {foreach item=a_c key=k from=$data_attr[$c[id]][value]}
            <span class="item orderCart_attr" val="{$a_c}" >{if $c.input_type eq 1}{$a_c}{else}{$c[select][$a_c]}{/if}</span>
            {/foreach}  
			</div>  
            </div> 
        </div>
        
      {/if}
  	
   {/foreach}
  </div>
  <!--end 产品属性-->
  {/if}
';
?>