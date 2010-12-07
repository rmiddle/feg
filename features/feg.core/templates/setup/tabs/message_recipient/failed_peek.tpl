<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formRecipientPeek" name="formRecipientPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveMessageRecipientFailurePeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

{$rec = DAO_CustomerRecipient::get($message.recipient_id)}
<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.customer_recipient.id.new')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('common.disabled')|capitalize}: </td>
		<td width="100%">
			<select name="recipient_is_disabled">
				<option value="0" {if $rec->is_disabled == 0}selected{/if}>{$translate->_('common.enable')|capitalize}</option>
				<option value="1" {if $rec->is_disabled == 1}selected{/if}>{$translate->_('common.disable')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.type')|capitalize}: </td>
			<select name="recipient_type">
				<option value="0" {if $rec->type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $rec->type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $rec->type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.address')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_address" value="{$rec->address|escape}" style="width:98%;"></td>
	</tr>
	{if $id}
		{$account = DAO_CustomerAccount::get($rec->account_id)}
	{else}
		{$account = DAO_CustomerAccount::get($customer_id)}
	{/if}
{if $active_worker->is_superuser}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.id')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_account_id" value="{$account->id}" style="width:98%;"></td>
	</tr>
{/if}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_number')|capitalize}: </td>
		<td width="100%">{$account->account_number}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_name')|capitalize}: </td>
		<td width="100%">{$account->account_name}</td>
	</tr>
</table>
<input type="hidden" name="recipient_export_filter" value="{$rec->export_filter}">

{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
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
