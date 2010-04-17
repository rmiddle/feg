<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<table cellpadding="0" cellspacing="0" border="0">
{if !empty($views)}
	{foreach from=$views item=view name=views}
		{if $smarty.foreach.results.iteration % 2}
			<tr>
		{/if}
			<td valign="top" width="50%">
				<div id="view{$view->id}">
					{$view->render()}
				</div>
			</td>
		{if $smarty.foreach.results.iteration % 2}
			</tr>
		{/if}
	{/foreach}
{/if}
<table cellpadding="0" cellspacing="0" border="0">
<br>
<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>

</script>