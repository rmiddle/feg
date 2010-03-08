<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="stats">
Stats Template.
</div> 
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top" width="100%">
			<div id="postfix_stats"></div><br>
		</td>
		<td valign="top" width="100%">
			<div id="postfix_stats1"></div><br>
		</td>
		<td valign="top" width="100%">
			<div id="postfix_stats2"></div><br>
		</td>
		<td valign="top" width="100%">
			<div id="postfix_stats3"></div><br>
		</td>
	</tr>
</table>


<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
$(document).ready(function() {
	$("#postfix_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 1000);
});
$(document).ready(function() {
	$("#postfix_stats1").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_stats1").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 1000);
});
$(document).ready(function() {
	$("#postfix_stats2").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_stats2").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 1000);
});
$(document).ready(function() {
	$("#postfix_stats3").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_stats3").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 1000);
});
</script>