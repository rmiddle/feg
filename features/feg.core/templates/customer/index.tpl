<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="CustomerTabs">
		{foreach from=$tab_manifests item=tab_manifest}
			{$tabs[] = $tab_manifest->params.uri}
			<li><a href="{devblocks_url}ajax.php?c=display&a=showTab&ext_id={$tab_manifest->id}&ticket_id={$ticket->id}{/devblocks_url}"><i>{$tab_manifest->params.title|devblocks_translate|escape:'quotes'}</i></a></li>
		{/foreach}
</div> 
<br>

{$tab_selected_idx=0}
{foreach from=$tabs item=tab_label name=tabs}
	text
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
