<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		{*<td valign="top" width="60%">*}
		<td valign="top">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td valign="top" colspan="3">
						<div id="showfaxque"></div>
						Fax Statics:<br>
						<div id="showfaxstats"></div>
					</td>
				</tr>
				<tr>
					<td valign="top">
						Mail Statics:<br>
						<div id="mail_queue_stats"></div>
						<div id="mail_stats"></div><br>
					</td>
					<td valign="top">
						SNPP (Paging) Statics: <br>
						<div id="snpp_queue_stats"></div>
						<div id="snpp_stats"></div><br>
					</td>
					<td valign="top">
						Hardware Statics: <br>
						<div id="hardware_stats"></div><br>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top">
			{if !empty($views)}
				{foreach from=$views item=view name=views}
					<div id="view{$view->id}">
						{$view->render()}
					</div>
				{/foreach}
			{/if}
		</td>
	</tr>
</table>


<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
$(document).ready(function() {
	$("#mail_queue_stats").load("{devblocks_url}ajax.php?c=stats&a=showMailQueueStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#mail_queue_stats").load("{devblocks_url}ajax.php?c=stats&a=showMailQueueStats{/devblocks_url}");
	}, 5000);
});
{*$(document).ready(function() {
	$("#mail_stats").load("{devblocks_url}ajax.php?c=stats&a=showMailStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#mail_stats").load("{devblocks_url}ajax.php?c=stats&a=showMailStats{/devblocks_url}");
	}, 60000);
});
*}
$(document).ready(function() {
	$("#snpp_queue_stats").load("{devblocks_url}ajax.php?c=stats&a=showSNPPQueueStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#snpp_queue_stats").load("{devblocks_url}ajax.php?c=stats&a=showSNPPQueueStats{/devblocks_url}");
	}, 5000);
});
{*$(document).ready(function() {
	$("#snpp_stats").load("{devblocks_url}ajax.php?c=stats&a=showSNPPStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#snpp_stats").load("{devblocks_url}ajax.php?c=stats&a=showSNPPStats{/devblocks_url}");
	}, 60000);
});
*}
$(document).ready(function() {
	$("#showfaxque").load("{devblocks_url}ajax.php?c=stats&a=showFaxQue{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#showfaxque").load("{devblocks_url}ajax.php?c=stats&a=showFaxQue{/devblocks_url}");
	}, 5000);
});
{*$(document).ready(function() {
	$("#showfaxstats").load("{devblocks_url}ajax.php?c=stats&a=showFaxStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#showfaxstats").load("{devblocks_url}ajax.php?c=stats&a=showFaxStats{/devblocks_url}");
	}, 60000);
});
*}
</script>