<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.id')|capitalize} </td>
		<td>{$id}</td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.est_account_id')|capitalize} </td>
		<td>
			{if isset($message->params['account_name'])}
				{if $active_worker->hasPriv('core.access.customer.create')}
					<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountFailurePeek" name="formAccountFailurePeek">
						<input type="hidden" name="c" value="account">
						<input type="hidden" name="a" value="createNewCustomer">
						{if isset($message->params['account_name'])}
							<input type="hidden" name="account_name" value="{$message->params['account_name']}">
						{/if}
						<button type="submit"><span class="feg-sprite sprite-check"></span>
							{$translate->_('feg.message.create_account')} - {$message->params['account_name']}
						</button>
					</form>
				{else}{$message->params['account_name']}
				{/if}
			{else}{$translate->_('feg.message_recipient.status_unknown')|capitalize}
			{/if}
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
{if $active_worker->hasPriv('core.access.message.assign')}
	<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountFailurePeek" name="formAccountFailurePeek" onsubmit="return false;">
		<input type="hidden" name="c" value="stats">
		<input type="hidden" name="a" value="saveAccountFailurePeek">
		<input type="hidden" name="id" value="{$id}">
		<input type="hidden" name="view_id" value="{$view_id}">
		<button type="submit"><span class="feg-sprite sprite-check"></span>{$translate->_('feg.message.select_account')}</button>
	</form>
{/if}

<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.cancel')|capitalize}</button>

<br>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Select Account'); 
	});
</script>
