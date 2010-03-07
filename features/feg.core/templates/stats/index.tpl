<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div> 

<div id="stats">
Stats Template.
</div> 
<div id="postfix_stats">
</div>

<br>

{include file="file:$core_tpl/whos_online.tpl"}

<script>
 $(document).ready(function() {
 	 $("#responsecontainer").load("ajax.php");
   var refreshId = setInterval(function() {
	  $("#postfix_stats").load({devblocks_url}ajax.php?c=setup&a=showTab&ext_id={$tab_manifest->id}&request={$request_path|escape:'url'}{/devblocks_url});
   }, 5000);
});
</script>
