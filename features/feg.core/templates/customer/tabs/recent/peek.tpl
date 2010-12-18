{if $display_view}
<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Audit Log</a></li>
	</ul>
		
    <div id="ticketPeekTab1">
{/if}
{if $active_worker->hasPriv('core.access.message_recipient.permfail')}
		<button type="button" onclick="$('#message_reciptient_peek_{$id}_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=setMessageRecipientStatus&id={$id}&status=6&view_id={$view->id|escape:'url'}{/devblocks_url}');genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('feg.message_recipient.submit.permfail')}</button>
{/if}
	<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>

{$translate->_('feg.message_recipient.id')|capitalize}: {if $id}{$id}{else}{$translate->_('feg.customer_recipient.id.new')|capitalize}{/if}<br>
<div id="message_reciptient_peek_{$id}_status">
{$translate->_('feg.message_recipient.send_status')|capitalize}:{include file="file:$core_tpl/internal/feg/display_send_status.tpl" message_recipient_id=$result.mr_id}<br>
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

{if $display_view}
    <div id="ticketPeekTab2" style="display:none;">
			<div id="view{$view->id}">{$view->render()}</div>
	</div>
{/if}

<script language="JavaScript1.2" type="text/javascript">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Message Recipient");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
</script>
