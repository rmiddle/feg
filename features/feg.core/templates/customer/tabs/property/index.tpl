<div id="customerData">
<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="customer">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="feg.customer.tab.property">
<input type="hidden" name="action" value="saveCustomerAccount">
<input type="hidden" name="customer_id" value="{$customer_id}">
<input type="hidden" name="account_is_disabled" value="{if $customer->import_source == 0}0{else}{$customer->is_disabled}{/if}">
<input type="hidden" name="and_close" value="0">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_number')|capitalize}: </td>
		<td width="100%"><input type="text" name="customer_account_number" value="{$customer->account_number|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.account_name')|capitalize}: </td>
		<td width="100%"><input type="text" name="customer_account_name" value="{if isset($account_number) && ($customer->import_source == 0)}{$account_number}{else}{$customer->account_name|escape}{/if}" style="width:98%;"></td>
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
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.customer_account.disabled')|capitalize}: </td>
		<td width="100%">
			{if $customer->is_disabled == 0 || $customer->import_source == 0}<span class="feg-sprite sprite-check"></span>{$translate->_('common.enable')|capitalize}{else}<span class="feg-sprite sprite-delete"></span>{$translate->_('common.disable')|capitalize}{/if}
		</td>
	</tr>
</table>

{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=false}
<br>
{if $active_worker->hasPriv('acl.core.access.customer.update')}
<button type="submit"><span class="feg-sprite sprite-check"></span> *{$translate->_('common.save_changes')|capitalize}</button>
<button type="button" onclick="this.form.and_close.value='1';this.form.submit();"><span class="feg-sprite sprite-check"></span>{$translate->_('common.save_changes')|capitalize} and close</button>
{/if}
{if $active_worker->hasPriv('acl.core.access.customer.disable')}
{if $customer->is_disabled == 0 || $customer->import_source == 0}
<button type="button" onclick="this.form.account_is_disabled.value='1';this.form.submit();"><span class="feg-sprite sprite-delete"></span> {$translate->_('common.disable')|capitalize}</button>
{else}
<button type="button" onclick="this.form.account_is_disabled.value='0';this.form.submit();"><span class="feg-sprite sprite-check"></span>{$translate->_('common.enable')|capitalize}</button>
{/if}
{/if}

<br>
</form>
</div>
{if $active_worker->hasPriv('acl.core.access.customer.update')}
<br>
* {$translate->_('feg.customer_recipient.savechanges.warn')}
{/if}
<br>
