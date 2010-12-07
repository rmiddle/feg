<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formRecipientPeek" name="formRecipientPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveMessageRecipientFailurePeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

{$rec = DAO_CustomerRecipient::get($message->recipient_id)}
<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.customer_recipient.id.new')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">
			{if $rec->type == '0'}{$translate->_('recipient.type.email')|capitalize}{/if}
			{if $rec->type == '1'}{$translate->_('recipient.type.fax')|capitalize}{/if}
			{if $rec->type == '2'}{$translate->_('recipient.type.snpp')|capitalize}{/if}
		</td>
		<td width="100%" nowrap="nowrap"> - {$rec->address|escape}</td>
	</tr>
	{if $id}
		{$account = DAO_CustomerAccount::get($rec->account_id)}
	{else}
		{$account = DAO_CustomerAccount::get($customer_id)}
	{/if}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">
			{$account->account_number}
		</td>
		<td width="100%" nowrap="nowrap">
			- {$account->account_name}
		</td>
	</tr>
</table>

<br>
<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
{if $active_worker->is_superuser}
	<button type="button" onclick="if(confirm('Are you sure you want to delete this Customers Recipient?')){literal}{{/literal}this.form.do_delete.value='1';genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>
{/if}
<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Recipient'); 
	} );
</script>
