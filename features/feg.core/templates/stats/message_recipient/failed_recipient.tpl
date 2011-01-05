<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formRecipientPeek" name="formRecipientPeek" onsubmit="return false;">
<input type="hidden" name="c" value="stats">
<input type="hidden" name="a" value="saveMessageRecipientFailurePeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="retry" value="0">

{if $active_worker->hasPriv('core.access.message_recipient.retry')}
<button type="button" onclick="genericPanel.dialog('close');this.form.retry.value='3';genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('feg.message_recipient.submit.retry')}</button>
{/if}
{if $active_worker->hasPriv('core.access.message_recipient.permfail')}
	<button type="button" onclick="genericPanel.dialog('close');this.form.retry.value='6';genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('feg.message_recipient.submit.permfail')}</button>
{/if}
	<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>
{$rec = DAO_CustomerRecipient::get($message_recipient->recipient_id)}
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
			{if $rec->type == '255'}{$translate->_('recipient.type.slave')|capitalize}{/if}
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
			{$translate->_('feg.customer_account.id')|capitalize}:
		</td>
		<td width="100%" nowrap="nowrap">
			{$account->account_number} - {$account->account_name}
		</td>
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

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','{$translate->_('feg.message_recipient.title.failed')|capitalize}'); 
	} );
</script>
