<div id="customerData">
<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="customer">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="feg.customer.tab.property">
<input type="hidden" name="action" value="saveCustomerAccount">
<input type="hidden" name="customer_id" value="{$customer_id}">
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="and_close" value="0">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('common.disabled')|capitalize}: </td>
		<td width="100%">
			<select name="account_is_disabled">
				<option value="0" {if $rec->is_disabled == 0}selected{/if}>{$translate->_('common.enable')|capitalize}</option>
				<option value="1" {if $rec->is_disabled == 1}selected{/if}>{$translate->_('common.disable')|capitalize}</option>
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
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.import_source')|capitalize}: </td>
		<td width="100%">
			<select name="customer_account_import_source">
				{foreach from=$import_source item=import}
					<option value="{$import->id}" {if $customer->import_source == $import->id}selected="selected"{/if}>{$import->name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	
</table>

{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
<br>
<button type="submit"><span class="feg-sprite sprite-check"></span> {$translate->_('common.save_changes')|capitalize}</button>
<button type="button" onclick="this.form.and_close.value='1';this.form.submit();"><span class="feg-sprite sprite-check"></span>{$translate->_('common.save_changes')|capitalize} and close</button>
<button type="button" onclick="if(confirm('Are you sure you want to delete this Account?')){literal}{{/literal}this.form.do_delete.value='1';this.form.submit();{literal}}{/literal}"><span class="feg-sprite sprite-forbidden"></span> Delete Customer</button>

<br>
</form>
</div>

<br>
