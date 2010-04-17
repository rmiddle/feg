<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

{if !empty($views)}
	{foreach from=$views item=view name=views}
		<div id="view{$view->id}">
			{$view->render()}
		</div>
	{/foreach}
{/if}
<br>
<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>

</script>