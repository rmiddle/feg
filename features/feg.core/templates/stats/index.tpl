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
 $(document).ready(function() {
 	 $("#postfix_stats").load("ajax.php");
   var refreshId = setInterval(function() {
	  $("#postfix_stats").load(ajax.php?c=stats&a=showPostfixStats);
   }, 5000);
});
</script>
