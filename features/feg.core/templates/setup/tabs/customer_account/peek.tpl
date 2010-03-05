<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountPeek" name="formAccountPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveAccountPeek">
<input type="hidden" name="id" value="{$recipient->id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('account.account_name')|capitalize}:</b> </td>
		<td width="100%"><input type="text" name="first_name" value="{$account->name|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('account.address')|capitalize}: </td>
		<td width="100%"><input type="text" name="title" value="{$account->address|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('recipient.notes')}</b>: </td>
		<td width="100%"><input type="text" name="email" value="{$recipient->notes|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.type')|capitalize}: </td>
		<td width="100%"><input type="text" name="last_name" value="{$recipient->type|escape}" style="width:98%;"></td>
			<select name="is_superuser">
				<option value="0" {if $recipient->type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $recipient->type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $recipient->type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
			</select>
		</td>
	</tr>
	
</table>

{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
<br>

<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Recipient'); 
	} );
</script>
