<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAccountFailurePeek" name="formAccountFailurePeek" onsubmit="return false;">
<input type="hidden" name="c" value="stats">
<input type="hidden" name="a" value="saveAccountFailurePeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">

{if $active_worker->hasPriv('core.access.customer.create)}
<button type="button" onclick="{devblocks_url}{/devblocks_url}customer/{$result.l_account_id}/property{if isset($message->params.account_name)}/{$message->params.account_name}{/if}"><span class="feg-sprite sprite-check"></span>{$translate->_('feg.message.create_account')}</button>
{/if}
{if $active_worker->hasPriv('core.access.message.assign')}
	<button type="button" onclick="genericPanel.dialog('close');this.form.retry.value='6';genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-check"></span>{$translate->_('feg.message.select_account')}</button>
{/if}


params

<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.cancel')|capitalize}</button>
<br>
<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.id')|capitalize} </td>
		<td>{$id}</td>
	</tr>
	<tr>
		<td nowrap="nowrap" align="right">{$translate->_('feg.message.est_account_id')|capitalize} </td>
		<td>{if isset($message->params['account_name'])}{$message->params['account_name']} {else}{$translate->_('feg.message_recipient.status_unknown')|capitalize}{/if}</td>
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
		genericPanel.dialog('option','title','Select Account'); 
	} );
</script>
