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
			<select name="recipient_type">
				<option value="0" {if $customer_recipient->type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $customer_recipient->type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $customer_recipient->type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.export_type')|capitalize}: </td>
			<select name="recipient_export_type">
				{foreach from=$export_type item=export name=export_id}
					{if $customer_recipient->type == $export->recipient_type}
						<option value="{$export->id}" {if $customer_recipient->export_type == $export->id}selected{/if}>{$export->name}</option>
					{/if}
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.address_to')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_address_to" value="{$customer_recipient->address_to|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.address')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_address" value="{$customer_recipient->address|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('recipient.subject')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_subject" value="{$customer_recipient->subject|escape}" style="width:98%;"></td>
	</tr>
{if $active_worker->is_superuser}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">*{$translate->_('feg.customer_account.id')|capitalize}: </td>
		<td width="100%"><input type="text" name="recipient_account_id" value="{$account->id}" style="width:98%;"></td>
	</tr>
	<tr>
		<td colspan="2">* {$translate->_('feg.customer_account.id.warning')}</td>
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
{*Remove Delete it is dangerous{if $active_worker->is_superuser}
<button type="button" onclick="if(confirm('Are you sure you want to delete this Customers Recipient?')){literal}{{/literal}this.form.do_delete.value='1';genericPanel.dialog('close');genericAjaxPost('formRecipientPeek', 'view{$view_id}', '');{literal}}{/literal}"><span class="feg-sprite sprite-delete2"></span> {$translate->_('common.delete')|capitalize}</button>
{/if}*}
<br>
</form>
</div>

{if $display_view}
    <div id="ticketPeekTab2" style="display:none;">
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
</script>
