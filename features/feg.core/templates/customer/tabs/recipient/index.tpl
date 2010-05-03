<form>
	<button type="button" onclick="genericAjaxPanel('c=customer&a=showRecipientPeek&customer_id={$customer_id}&id=0&view_id={$view->id|escape:'url'}',null,false,'500');"><span class="feg-sprite sprite-hand_paper"></span> Add Recipient</button>
</form>

<div id="view{$view->id}">{$view->render()}</div>
