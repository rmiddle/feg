<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="AccountTabs">
	<ul>
		<li><a href="{devblocks_url}ajax.php?c=account&a=showTabAccounts&request={$request_path|escape:'url'}{/devblocks_url}">{$translate->_('account.tab.account')|escape:'quotes'}</a></li>

		{$tabs = [Accounts]}

		{foreach from=$tab_manifests item=tab_manifest}
			{if !isset($tab_manifest->params.acl) || $worker->hasPriv($tab_manifest->params.acl)}
			{$tabs[] = $tab_manifest->params.uri}
			<li><a href="{devblocks_url}ajax.php?c=account&a=showTab&ext_id={$tab_manifest->id}&request={$request_path|escape:'url'}{/devblocks_url}">{$tab_manifest->params.title|devblocks_translate|escape:'quotes'}</a></li>
			{/if}
		{/foreach}
	</ul>
</div> 
<br>

{include file="file:$core_tpl/whos_online.tpl"}

{$tab_selected_idx=0}
{foreach from=$tabs item=tab_label name=tabs}
	{if $tab_label==$tab_selected}{$tab_selected_idx = $smarty.foreach.tabs.index}{/if}
{/foreach}

<script type="text/javascript">
	$(function() {
		var tabs = $("#accountTabs").tabs( { selected:{$tab_selected_idx} } );
	});
</script>
