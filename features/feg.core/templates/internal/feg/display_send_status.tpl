<td id="message_reciptient_{$message_recipient_id}_status">
{if $result.$column == 0}
	{$translate->_('feg.message_recipient.status_0')|capitalize}
{else if $result.$column == 1}
	{$translate->_('feg.message_recipient.status_1')|capitalize}
	{if $active_worker->hasPriv('core.access.message_recipient.retry')}
		<a href="javascript:;" onclick="$('#message_reciptient_{$message_recipient_id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$message_recipient_id}&status=3&view_id={$view->id|escape:'url'}{/devblocks_url}');">
		({$translate->_('feg.core.send_status.retry')|capitalize})</a>
	{/if}
{else if $result.$column == 2}
	{$translate->_('feg.message_recipient.status_2')|capitalize}
	{if $active_worker->hasPriv('core.access.message_recipient.resend')}
		<a href="javascript:;" onclick="$('#message_reciptient_{$message_recipient_id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$message_recipient_id}&status=4&view_id={$view->id|escape:'url'}{/devblocks_url}');">
		({$translate->_('feg.core.send_status.resend')|capitalize})</a>
	{/if}
{else if $result.$column == 3}
	{$translate->_('feg.message_recipient.status_3')|capitalize}
{else if $result.$column == 4}
	{$translate->_('feg.message_recipient.status_4')|capitalize}
{else if $result.$column == 5}
	{$translate->_('feg.message_recipient.status_5')|capitalize}
{else if $result.$column == 6}
	{$translate->_('feg.message_recipient.status_6')|capitalize}
{else}
	{$translate->_('feg.message_recipient.status_unknown')|capitalize}
{/if}
</td>