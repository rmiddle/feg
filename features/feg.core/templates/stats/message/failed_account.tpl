<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountFailurePeek" name="formAccountFailurePeek">
<input type="hidden" name="c" value="account">
<input type="hidden" name="a" value="createNewCustomer">
{if isset($message->params['account_name'])}
	<input type="hidden" name="account_name" value="{$message->params['account_name']}">
{/if}

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">&nbsp;</td>
		<td>
			{if $active_worker->hasPriv('core.access.message.assign')}
				<a href="javascript:;" onclick="genericAjaxPanel('c=account&a=createNewCustomer&account_name={$message->params['account_name']}|escape:'url'}',null,false,'550');">
				<b>{$translate->_('feg.message.select_account')}</b></a>&nbsp;
			{/if}
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">
			{if $active_worker->hasPriv('core.access.customer.create')}
				{if isset($message->params['account_name'])}
					<a href="javascript:;" onclick="$('#formAccountFailurePeek').trigger('submit');">
					<b>{$translate->_('feg.message.create_account')}:</b></a>&nbsp;
				{/if}
			{else}{$translate->_('feg.message.est_account_id')|capitalize}&nbsp;
			{/if}
		</td>
		<td>
			{if isset($message->params['account_name'])}
				{if $active_worker->hasPriv('core.access.customer.create')}
					<a href="javascript:;" onclick="genericPanel.dialog('close');genericAjaxGet('', '{devblocks_url}index.php?c=account&a=createNewCustomer&account_name={$message->params['account_name']}{/devblocks_url}');">
					<b>{$message->params['account_name']}</b></a>&nbsp;
				{else}{$message->params['account_name']}&nbsp;
				{/if}
			{else}{$translate->_('feg.message_recipient.status_unknown')|capitalize}
			{/if}
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.id')|capitalize} </td>
		<td>{$id}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" valign="top" align="right">
			{$translate->_('feg.message.message')|capitalize}:
		</td>
		<td width="100%">
			{foreach from=$message_lines item=line name=line_id}
				{$line}<br>
			{/foreach}
		</td>
	</tr>
</table>
<br>

<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span>{$translate->_('common.cancel')|capitalize}</button>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Select Account'); 
	});
</script>
