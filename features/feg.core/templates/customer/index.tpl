<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="homeTabs">
	<ul>
		<li><a href="{devblocks_url}ajax.php?c=home&a=showTabNotifications&request={$request_path|escape:'url'}{/devblocks_url}">{$translate->_('home.tab.notifications')|escape:'quotes'}</a></li>

		{$tabs = [notifications]}

		{foreach from=$tab_manifests item=tab_manifest}
			{if !isset($tab_manifest->params.acl) || $worker->hasPriv($tab_manifest->params.acl)}
			{$tabs[] = $tab_manifest->params.uri}
			<li><a href="{devblocks_url}ajax.php?c=home&a=showTab&ext_id={$tab_manifest->id}&request={$request_path|escape:'url'}{/devblocks_url}">{$tab_manifest->params.title|devblocks_translate|escape:'quotes'}</a></li>
			{/if}
		{/foreach}

		{if 1||$active_worker->hasPriv('core.home.workspaces')}
		{foreach from=$workspaces item=workspace}
			{$tabs[] = $tab_manifest->params.uri}
			<li><a href="{devblocks_url}ajax.php?c=home&a=showWorkspaceTab&workspace={$workspace|escape:'url'}{/devblocks_url}"><i>{$workspace|escape}</i></a></li>
		{/foreach}
		{/if}
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
		var tabs = $("#homeTabs").tabs( { selected:{$tab_selected_idx} } );
	});
</script>
