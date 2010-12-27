<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="CustomerTabs">
	<ul>
		{foreach from=$tab_manifests item=tab_manifest}
			{$tabs[] = $tab_manifest->params.uri}
			<li><a href="{devblocks_url}ajax.php?c=customer&a=showTab&ext_id={$tab_manifest->id}&customer_id={$customer_id}{if isset($account_number)}&account_number={$account_number}{/if}{/devblocks_url}"><i>{$tab_manifest->params.title|devblocks_translate|escape:'quotes'}</i></a></li>
		{/foreach}
	</ul>
</div> 
<br>

{$tab_selected_idx=0}
{foreach from=$tabs item=tab_label name=tabs}
	{if $tab_label==$tab_selected}{$tab_selected_idx = $smarty.foreach.tabs.index}{/if}
{/foreach}

{include file="file:$core_tpl/whos_online.tpl"}

<script type="text/javascript">
	$(function() {
		var tabs = $("#CustomerTabs").tabs( { selected:{$tab_selected_idx} } );
	});
</script>
