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
					<a href="javascript:;" onclick="genericAjaxPanel('c=account&a=createNewCustomer&account_name={$message->params['account_name']}|escape:'url'}',null,false,'550');">
					{$translate->_('feg.message.create_account')}: {$message->params['account_name']}</a>&nbsp;
				{else}{$message->params['account_name']}
				{/if}
				{if $active_worker->hasPriv('core.access.message.assign')}
					<a href="javascript:;" onclick="genericAjaxPanel('c=account&a=createNewCustomer&account_name={$message->params['account_name']}|escape:'url'}',null,false,'550');">
					{$translate->_('feg.message.select_account')}</a>&nbsp;
				{/if}
			{else}{$translate->_('feg.message_recipient.status_unknown')|capitalize}
			{/if}
		</td>
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
				
<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Select Account'); 
	});
</script>
