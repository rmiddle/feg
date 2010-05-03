<form>
	<button type="button" onclick="genericAjaxPanel('c=recipient&a=showRecipientPeek&customer_id={$customer_id}&id=0&view_id={$view->id|escape:'url'}',null,false,'500');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/businessman_add.gif{/devblocks_url}" align="top"> Add Recipient</button>
</form>

<div id="view{$view->id}">{$view->render()}</div>
