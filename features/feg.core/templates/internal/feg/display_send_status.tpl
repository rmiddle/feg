<div id="message_reciptient_{$message_recipient_id}_status">
{if $result.$column == 0}
	{$translate->_('feg.core.send_status.new')|capitalize}
{else if $result.$column == 1}
	{$translate->_('feg.core.send_status.fail')|capitalize}
		<a href="javascript:;" onclick="$('#message_reciptient_{$message_recipient_id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$message_recipient_id}&status=3&view_id={$view->id|escape:'url'}{/devblocks_url}');">
	({$translate->_('feg.core.send_status.retry')|capitalize})</a>
{else if $result.$column == 2}
	{$translate->_('feg.core.send_status.successful')|capitalize}
		<a href="javascript:;" onclick="$('#message_reciptient_{$message_recipient_id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$message_recipient_id}&status=4&view_id={$view->id|escape:'url'}{/devblocks_url}');">
	({$translate->_('feg.core.send_status.resend')|capitalize})</a>
{else if $result.$column == 3}
	{$translate->_('feg.core.send_status.retry')|capitalize}
{else if $result.$column == 4}
	{$translate->_('feg.core.send_status.resend')|capitalize}
{/if}
</div>