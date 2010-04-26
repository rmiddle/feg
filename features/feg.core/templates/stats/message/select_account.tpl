<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountFailurePeek" name="formAccountFailurePeek" onsubmit="return false;">
<input type="hidden" name="c" value="stats">
<input type="hidden" name="a" value="saveAccountFailurePeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.id')|capitalize} </td>
		<td>{$id}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.address')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_address" value="{$rec->address|escape}" style="width:98%;"></td>
	</tr>
		{*$account = DAO_CustomerAccount::get($msg->account_id)*}
{if $active_worker->is_superuser}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.id')|capitalize}: </td>
{/if}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.message.message')|capitalize}: </td>
		<td width="100%">{}</td>
	</tr>
</table>

<br>
<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formAccountFailurePeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('feg.message.select_account')}</button>
<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Select Account'); 
	} );
</script>
