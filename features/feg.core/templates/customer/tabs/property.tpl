<div id="customerData">
<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formCustomerAccount" name="formCustomerAccount" onsubmit="return false;">
<input type="hidden" name="c" value="customer">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="feg.customer.tab.property">
<input type="hidden" name="action=" value="saveCustomerAccount">
<input type="hidden" name="customer_id" value="{$customer_id}">
<input type="hidden" name="do_delete" value="0">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('common.disabled')|capitalize}: </td>
		<td width="100%">
			<select name="account_is_disabled">
				<option value="0" {if !$customer->is_disabled}selected{/if}>{$translate->_('common.no')|capitalize}</option>
				<option value="1" {if $customer->is_disabled}selected{/if}>{$translate->_('common.yes')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer.customer.account_number')|capitalize}: </td>
		<td width="100%"><input type="text" name="customer_account_number" value="{$customer->account_number|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer.customer.account_name')|capitalize}: </td>
		<td width="100%"><input type="text" name="customer_account_name" value="{$customer->account_name|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer.customer.import_filter')|capitalize}: </td>
		<td width="100%">
			<select name="customer_account_import_filter">
				<option value="0" {if $customer->import_filter == 0}selected{/if}>{$translate->_('common.default')|capitalize}</option>
			</select>
		</td>
	</tr>
	
</table>

{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
<br>
<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formCustomerAccount', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
{if $active_worker->is_superuser}
	<button type="button" onclick="if(confirm('Are you sure you want to delete this Customers Account?')){literal}{{/literal}this.form.do_delete.value='1';{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>
{/if}
<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>

<br>
</form>
</div>

<br>
