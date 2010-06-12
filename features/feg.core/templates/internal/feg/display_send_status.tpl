{if $result.$column == 0}
	{$translate->_('feg.core.send_status.new')|capitalize}
{else if $result.$column == 1}
	{$translate->_('feg.core.send_status.fail')|capitalize}
		<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$recipient_id}&view_id={$view->id|escape:'url'}',null,false,'550');">
	({$translate->_('feg.core.send_status.retry')|capitalize})</a>
{else if $result.$column == 2}
	{$translate->_('feg.core.send_status.successful')|capitalize}
		<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$recipient_id}&view_id={$view->id|escape:'url'}',null,false,'550');">
	({$translate->_('feg.core.send_status.resend')|capitalize})</a>
{/if}

