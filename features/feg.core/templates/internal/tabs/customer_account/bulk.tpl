<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formBatchUpdate" name="formBatchUpdate">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="doAccountBulkUpdate">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="ids" value="{$ids}">

<h2>{$translate->_('common.bulk_update.with')|capitalize}:</h2>
<label><input type="radio" name="filter" value="" {if empty($ids)}checked{/if}> {$translate->_('common.bulk_update.filter.all')}</label> 
<label><input type="radio" name="filter" value="checks" {if !empty($ids)}checked{/if}> {$translate->_('common.bulk_update.filter.checked')}</label> 
<br>
<br>

<H2>{$translate->_('common.bulk_update.do')|capitalize}:</H2>
<table cellspacing="0" cellpadding="2" width="100%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{'common.disable'|devblocks_translate|capitalize}:</td>
		<td width="100%">
			<select name="account_is_disabled">
				<option value="">&nbsp;</option>
				<option value="0">{$translate->_('common.enable')}</option>
				<option value="1">{$translate->_('common.disable')}</option>
			</select>
			
			<button type="button" onclick="this.form.account_is_disabled.selectedIndex=1;">{'common.enable'|devblocks_translate|capitalize}</button>
			<button type="button" onclick="this.form.account_is_disabled.selectedIndex=2;">{'common.disable'|devblocks_translate|capitalize}</button>
		</td>
	</tr>
</table>


{include file="file:$core_tpl/internal/custom_fields/bulk/form.tpl" bulk=true}	

<br>

<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formBatchUpdate','view{$view_id}');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"{$translate->_('common.bulk_update')|capitalize|escape}"); 
	} );
</script>
