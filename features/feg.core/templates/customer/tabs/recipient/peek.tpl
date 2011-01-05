{if $display_view}
<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Audit Log</a></li>
	</ul>
		
    <div id="ticketPeekTab1">
{/if}
<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formRecipientPeek" name="formRecipientPeek" onsubmit="return false;">
<input type="hidden" name="c" value="customer">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="feg.customer.tab.recipient">
<input type="hidden" name="action" value="saveRecipientPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="recipient_is_disabled" value="{$customer_recipient->is_disabled}">

{if $id}
	{$account = DAO_CustomerAccount::get($customer_recipient->account_id)}
{else}
	{$account = DAO_CustomerAccount::get($customer_id)}
{/if}
<input type="hidden" name="recipient_account_id" value="{$account->id}">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
{if $active_worker->is_superuser}
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.customer_recipient.id.new')|capitalize}{/if}</td>
	</tr>
{/if}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.type')|capitalize}: </td>
			<select name="recipient_type" id="recipient_type">
				<option value="0" {if $customer_recipient->type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $customer_recipient->type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $customer_recipient->type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
				<option value="255" {if $customer_recipient->type == '255'}selected{/if}>{$translate->_('recipient.type.slave')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.export_type')|capitalize}: </td>
		<td><div id="div_export_recipient_type"></div></td>
	</tr>
	<tr id='tr_address_customer_input' {if $customer_recipient->type != '255'}style="display:none"{/if}>
		<td width="0%" nowrap="nowrap" align="right">
				{$translate->_('recipient.type.address.slave')|capitalize}:
		</td>
		<td id='tr_address_input' width="100%">
			<input type="text" name="text_address_customer_input" id="text_address_customer_input" value="{*$customer_recipient->address|escape*}" style="width:98%;">
		</td>
	</tr>
	<tr id='tr_address_account_name' {if $customer_recipient->type != '255'}style="display:none"{/if}>
		<td nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_number')|capitalize}</td>
		<td>
			<div id="assign_to_account_results_name">&nbsp;</div>
		</td>
	</tr>
	<tr id='tr_address_account_number' {if $customer_recipient->type != '255'}style="display:none"{/if}>
		<td nowrap="nowrap" align="right">
				{$translate->_('feg.customer_account.account_name')}&nbsp;
		</td>
		<td>
				<span id="assign_to_account_results_number">&nbsp;</span>&nbsp;
		</td>
	</tr>
	<tr id='tr_address_to' {if $customer_recipient->type == '2'  || $customer_recipient->type == '255'}style="display:none"{/if}>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.address_to')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_address_to" value="{$customer_recipient->address_to|escape}" style="width:98%;"></td>
	</tr>
	<tr id='tr_address' {if $customer_recipient->type == '255'}style="display:none"{/if}>
		<td id='tr_address_label' width="0%" nowrap="nowrap" align="right">
				{if $customer_recipient->type == '0'}{$translate->_('recipient.type.address.email')|capitalize}:{/if}
				{if $customer_recipient->type == '1'}{$translate->_('recipient.type.address.fax')|capitalize}:{/if}
				{if $customer_recipient->type == '2'}{$translate->_('recipient.type.address.snpp')|capitalize}:{/if}
		</td>
		<td id='tr_address_input' width="100%">
			<input type="text" name="recipient_address"  value="{$customer_recipient->address|escape}" style="width:98%;">
		</td>
	</tr>
	<tr id='tr_subject' {if $customer_recipient->type == '2'  || $customer_recipient->type == '255'}style="display:none"{/if}>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.subject')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_subject" value="{$customer_recipient->subject|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_number')|capitalize}: </td>
		<td width="100%">{$account->account_number}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_name')|capitalize}: </td>
		<td width="100%">{$account->account_name}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.disabled')|capitalize}: </td>
		<td width="100%">
			{if $customer_recipient->is_disabled == 0}<span class="feg-sprite sprite-check"></span>{$translate->_('common.enable')|capitalize}{else}<span class="feg-sprite sprite-delete_gray"></span>{$translate->_('common.disable')|capitalize}{/if}
		</td>
	</tr>
</table>
	
{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
<br>

{if $active_worker->hasPriv('core.access.recipient.update')}
<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-check"></span> {$translate->_('common.save_changes')}</button>
{/if}
{if $active_worker->hasPriv('core.access.recipient.disable')}
{if $customer_recipient->is_disabled == 0}
<button type="button" onclick="this.form.recipient_is_disabled.value='1';genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.disable')|capitalize}</button>
{else}
<button type="button" onclick="this.form.recipient_is_disabled.value='0';genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-check"></span>{$translate->_('common.enable')|capitalize}</button>
{/if}
{/if}
<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete"></span>  {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>
</div>

{if $display_view}
    <div id="ticketPeekTab2" style="display:none">
			<div id="view{$view->id}">{$view->render()}</div>
	</div>
{/if}

<script language="JavaScript1.2" type="text/javascript">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Recipient");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
	
	$(document).ready(function() {
		$("#text_address_customer_input").autocomplete({
			source: "{devblocks_url}ajax.php?c=account&a=searchCustomerJson{/devblocks_url}",
			minLength: 1,
			select: function( event, ui ) {
				var account = ui.item ? ui.item.value : this.value;
				$.getJSON("{devblocks_url}ajax.php?c=account&a=showCustomerJson&search="+account+"{/devblocks_url}", function(data) {
					$('#assign_to_account_results_name').text(data.account_name);
					$('#assign_to_account_results_number').text(data.account_number);
					$('#recipient_address').val(data.account_number);
				});
			}
		});
		$("#div_export_recipient_type").load("{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientType&type={$customer_recipient->type}&selected_type={$customer_recipient->export_type}{/devblocks_url}");
		$('#recipient_type').change(function() {
			var sel = $(this).val();
			switch (sel)
			{
				case "0": 	{*Email*}
					$("#tr_address").show();
					$("#tr_address_to").show();
					$("#tr_subject").show();
					$("#tr_address_account_name").hide();
					$("#tr_address_account_number").hide();
					$("#tr_address_customer_input").hide();
					$("#tr_address_label").text("{$translate->_('recipient.type.address.email')|capitalize}:");
					break
				case "1": 	{*Fax*}
					$("#tr_address").show();
					$("#tr_address_to").show();
					$("#tr_subject").show();
					$("#tr_address_account_name").hide();
					$("#tr_address_account_number").hide();
					$("#tr_address_customer_input").hide();
					$("#tr_address_label").text("{$translate->_('recipient.type.address.fax')|capitalize}:");
					break
				case "2": 	{*SNPP*}
					$("#tr_address").show();
					$("#tr_address_to").hide();
					$("#tr_subject").hide();
					$("#tr_address_account_name").hide();
					$("#tr_address_account_number").hide();
					$("#tr_address_customer_input").hide();
					$("#tr_address_label").text("{$translate->_('recipient.type.address.snpp')|capitalize}:");
					break
				case "255": 	{*SLAVE*}
					$("#tr_address").hide();
					$("#tr_address_to").hide();
					$("#tr_subject").hide();
					$("#tr_address_account_name").show();
					$("#tr_address_account_number").show();
					$("#tr_address_customer_input").show();
					break
				default: {*Should never be hit*}
			}
			$("#div_export_recipient_type").load("{devblocks_url}ajax.php?c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientType&type="+sel+"&selected_type={$customer_recipient->export_type}{/devblocks_url}");
		});
	});
</script>
