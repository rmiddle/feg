{if $active_worker->hasPriv('core.access.recipient.create')}
<form>
	<button type="button" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&customer_id={$customer_id}&id=0&view_id={$view->id|escape:'url'}',null,false,'500');"><span class="feg-sprite sprite-add"></span> {$translate->_('feg.customer_recipient.id.add')|capitalize}</button>
</form>
{/if}

<div id="view{$view->id}">{$view->render()}</div>
