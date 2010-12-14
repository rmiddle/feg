{if $display_view}
<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Audit Log</a></li>
	</ul>
		
    <div id="ticketPeekTab1">
{/if}
<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formMessageRecipientPeek" name="formMessageRecipientPeek" onsubmit="return false;">
<input type="hidden" name="c" value="customer">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="feg.customer.tab.recent.messages">
<input type="hidden" name="action" value="saveMessageRecipientPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

{if $active_worker->hasPriv('core.access.message_recipient.permfail')}
	<button type="button" onclick="genericPanel.dialog('close');this.form.retry.value='6';genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('feg.message_recipient.submit.permfail')}</button>
{/if}
	<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.customer_recipient.id.new')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_number')|capitalize}: </td>
		<td width="100%">{$account->account_number}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_name')|capitalize}: </td>
		<td width="100%">{$account->account_name}</td>
	</tr>
		<td width="0%" nowrap="nowrap" align="right">
			{if $recipient->type == '0'}{$translate->_('recipient.type.email')|capitalize}{/if}
			{if $recipient->type == '1'}{$translate->_('recipient.type.fax')|capitalize}{/if}
			{if $recipient->type == '2'}{$translate->_('recipient.type.snpp')|capitalize}{/if}
		</td>
		<td width="100%" nowrap="nowrap"> - {$recipient->address|escape}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" valign="top" align="right">{$translate->_('feg.message.message')|capitalize}: </td>
		<td width="100%">
			{foreach from=$message_lines item=line name=line_id}
				{$line}<br>
			{/foreach}
		</td>
	</tr>		
</table>
<br>
</form>
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
