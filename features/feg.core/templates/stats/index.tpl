<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="stats">
Stats Template.
</div> 
Postfix Stats: <div id="postfix_stats"></div><br>


<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
$(document).ready(function() {
	$("#postfix_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	var refreshId = setInterval(function() {
		$("#postfix_stats").load("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}");
	}, 1000);
});
</script>