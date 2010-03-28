<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="CustomerTabs">
CustomerTab
</div> 
<br>

{$tab_selected_idx=0}
{foreach from=$tabs item=tab_label name=tabs}
	{if $tab_label==$tab_selected}{$tab_selected_idx = $smarty.foreach.tabs.index}{/if}
{/foreach}

<div id="CustomerData">
CustomerData
</div> 


{include file="file:$core_tpl/whos_online.tpl"}

<script type="text/javascript">
	$(function() {
		var tabs = $("#CustomerTabs").tabs( { selected:{$tab_selected_idx} } );
	});
</script>
