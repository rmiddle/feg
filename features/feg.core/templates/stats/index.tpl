<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top" width="25%">
			<div id="postfix_mailq_stats"></div>
			<div id="postfix_sent_stats"></div><br>
		</td>
		<td valign="top" width="25%">
			<div id="showfaxquestats"></div>
			<div id="showfaxstats"></div>
		</td>
		<td valign="top" width="25%">
			<div id="postfix_stats2"></div><br>
		</td>
		<td valign="top" width="25%" align="right">
			<div id="postfix_stats3"></div><br>
		</td>
	</tr>
</table>


<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
$(document).ready(function() {
	$("#postfix_mailq_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixMailqStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_mailq_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixMailqStats{/devblocks_url}");
	}, 1000);
});
$(document).ready(function() {
	$("#postfix_sent_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_sent_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 300000);
});

$(document).ready(function() {
	$("#showfaxquestats").load("{devblocks_url}ajax.php?c=stats&a=showFaxQueStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#showfaxquestats").load("{devblocks_url}ajax.php?c=stats&a=showFaxQueStats{/devblocks_url}");
	}, 1000);
});
$(document).ready(function() {
	$("#showfaxstats").load("{devblocks_url}ajax.php?c=stats&a=showFaxStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#showfaxstats").load("{devblocks_url}ajax.php?c=stats&a=showFaxStats{/devblocks_url}");
	}, 300000);
});

</script>