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
