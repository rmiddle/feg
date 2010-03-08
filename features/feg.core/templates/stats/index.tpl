<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="stats">
Stats Template.
</div> 
<div id="postfix_stats">
PostfixStatsDiv
</div>

<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
function update() {
  $.get("{devblocks_url}ajax.php?c=stats&a=showPostfixStats{/devblocks_url}", function(data) {
    $("#postfix_stats").html(data);
    window.setTimeout(update, 5000);
  });
}
</script>