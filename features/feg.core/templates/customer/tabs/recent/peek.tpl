<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Audit Log</a></li>
	</ul>
		
<div id="ticketPeekTab1">
{if $active_worker->hasPriv('core.access.message_recipient.permfail')}
	{if $message_recipient->send_status == 1}
		<button type="button" onclick="$('#message_reciptient_{$id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$id}&status=6&goto_recent=3&view_id={$view->id|escape:'url'}{/devblocks_url}');genericPanel.dialog('close');"><span class="feg-sprite sprite-check"></span> {$translate->_('feg.core.send_status.retry')|capitalize}</button>
	{else if $message_recipient->send_status == 2}
		<button type="button" onclick="$('#message_reciptient_{$id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$id}&status=6&goto_recent=4&view_id={$view->id|escape:'url'}{/devblocks_url}');genericPanel.dialog('close');"><span class="feg-sprite sprite-check"></span>{$translate->_('feg.core.send_status.resend')|capitalize}</button>
	{/if}
	{if $message_recipient->send_status != 6}
		<button type="button" onclick="$('#message_reciptient_{$id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$id}&status=6&goto_recent=1&view_id={$view->id|escape:'url'}{/devblocks_url}');genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span>{$translate->_('feg.message_recipient.submit.permfail')|capitalize}</button>
	{/if}
{/if}
	<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.cancel')|capitalize}</button>
<br>

{$translate->_('feg.message_recipient.id')|capitalize}: {$id}<br>
<div id="message_reciptient_peek_{$id}_status">
	{$translate->_('feg.message_recipient.send_status')|capitalize}: 
	{if $message_recipient->send_status >= 0 && $message_recipient->send_status < 140}
		{$status_str = 'feg.message_recipient.status_'|cat:$message_recipient->send_status}
		{$translate->_($status_str)|capitalize}
	{else}
		{$translate->_('feg.message_recipient.status_unknown')|capitalize}
	{/if}
</td>

<br>
</div>
<br>
Account Info:<br>
{$translate->_('feg.customer_account.account_number')|capitalize}: {$account->account_number}<br>
{$translate->_('feg.customer_account.account_name')|capitalize}: {$account->account_name}<br>
<br>
Recipient Info:<br>
{$translate->_('feg.customer_recipient.type')|capitalize}: {if $recipient->type == '0'}{$translate->_('recipient.type.email')|capitalize}{/if}{if $recipient->type == '1'}{$translate->_('recipient.type.fax')|capitalize}{/if}{if $recipient->type == '2'}{$translate->_('recipient.type.snpp')|capitalize}{/if}<br>
{$translate->_('feg.customer_recipient.address_to')|capitalize}: {$recipient->address_to|capitalize}<br>
{$translate->_('feg.customer_recipient.address')|capitalize}: {$recipient->address|escape}<br>
{$translate->_('feg.customer_recipient.subject')|capitalize}: {$recipient->subject}<br>
<br>
Message Info:<br>
{$translate->_('feg.message.created_date')|capitalize}: {$message->created_date|devblocks_date}<br>
{$translate->_('feg.message.updated_date')|capitalize}: {$message->updated_date|devblocks_date}<br>
{$translate->_('feg.message.message')|capitalize}:<br>
{foreach from=$message_lines item=line name=line_id}
	{$line}<br>
{/foreach}
<br>
</div>

<div id="ticketPeekTab2" style="display:none;">
	<div id="view{$view->id}">{$view->render()}</div>
</div>

{* End div for the tab*}
</div>

<script language="JavaScript1.2" type="text/javascript">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Message Recipient");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
</script>
