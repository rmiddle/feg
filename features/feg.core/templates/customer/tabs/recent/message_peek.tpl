<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Audit Log</a></li>
	</ul>
		
<div id="ticketPeekTab1">
<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.cancel')|capitalize}</button>
<br>
Account Info:<br>
{$translate->_('feg.customer_account.account_number')|capitalize}: {$account->account_number}<br>
{$translate->_('feg.customer_account.account_name')|capitalize}: {$account->account_name}<br>
<br>
Message Info:<br>
{$translate->_('feg.message.created_date')|capitalize}: {$message->created_date|devblocks_date}<br>
{$translate->_('feg.message.updated_date')|capitalize}: {$message->updated_date|devblocks_date}<br>
{$translate->_('feg.message.import_status')|capitalize}:
<span id="span_message_import_status">
{$status_str = 'feg.message.import_status_'|cat:$message->import_status}
{$translate->_($status_str)|capitalize}&nbsp;
{if $message->import_status == 2}
	{if $active_worker->hasPriv('core.access.message.reprocess')}
		<a href="javascript:;" onclick="$('#span_message_import_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.standard.messages&action=setMessageStatus&id={$result.message_id}&status=0&view_id={$view->id|escape:'url'}{/devblocks_url}');">
	({$translate->_('feg.message.import_status.reprocess')|capitalize})</a>
	{/if}
{elseif $message->import_status == 1}			
	<a href="javascript:;" onclick="$('#span_message_import_status').load('{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.standard.messages&action=setMessageStatus&id={$result.message_id}&status=0&view_id={$view->id|escape:'url'}{/devblocks_url}');">
	({$translate->_('feg.message_recipient.status_retry')|capitalize})</a>
{/if}
</span>
<br>
{$translate->_('feg.message.message')|capitalize}:<br>
{foreach from=$message_lines item=line name=line_id}
	{$line}<br>
{/foreach}
<br>
</div>

<div id="ticketPeekTab2" style="display:none;">
	<div id="view{$view->id}">{$view->render()}</div>
</div>

{* End div for the tab*}
</div>

<script language="JavaScript1.2" type="text/javascript">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Message Recipient");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
</script>
