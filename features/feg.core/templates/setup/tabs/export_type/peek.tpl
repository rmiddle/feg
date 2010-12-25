<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formExportPeek" name="formExportPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveExportPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="export_type_is_disabled" value="{$export_type->is_disabled}">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.export_type.new_source')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('feg.export_type.name')}</b>: </td>
		<td width="100%"><input type="text" name="export_type_name" value="{$export_type->name|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.export_type.peek.recipient_type')|capitalize}: </td>
		<td width="100%">
			<select name="export_type_recipient_type">
				<option value="0" {if $export_type->recipient_type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $export_type->recipient_type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $export_type->recipient_type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
			</select>
		</td>
	</tr>
	{foreach from=$export_type->params item=param key=param_id}
		<tr>
			<td width="0%" nowrap="nowrap" align="right"><b>{$param_id}</b>: </td>
			<td width="100%"><input type="text" name="export_type_params_{$param_id}" value="{$param|escape}" style="width:98%;"></td>
		</tr>
	{/foreach}
		<tr>
			<td width="0%" nowrap="nowrap" align="right"><b></b>{$translate->_('feg.export_type.add_filter')|capitalize}: </td>
			<td width="100%">
				<select name="export_type_params_add">
					{foreach from=$export_type_params item=export_type_param key=export_type_param_id}
						{if $type == $export_type_param->recipient_type}
							<option value="{$export_type_param->id}">{$export_type_param->name}</option>
						{/if}
					{/foreach}
				</select>
		</td>
	</tr>
</table>

<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-check"></span> {$translate->_('common.save_changes')}</button>
{if $active_worker->is_superuser}
{if $export_type->is_disabled == 0}
<button type="button" onclick="this.form.export_type_is_disabled.value='1';genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-delete_gray"></span> {$translate->_('common.disable')|capitalize}</button>
{else}
<button type="button" onclick="this.form.export_type_is_disabled.value='0';genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');"><span class="feg-sprite sprite-check"></span>{$translate->_('common.enable')|capitalize}</button>
{/if}
{/if}
{*
{if $active_worker->is_superuser}
	<button type="button" onclick="if(confirm('Are you sure you want to delete this Export Type?')){literal}{{/literal}this.form.do_delete.value='1';genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>
{/if}
*}
<button type="button" onclick="genericPanel.dialog('close');"><span class="feg-sprite sprite-delete"></span>  {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Export Type Editor");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
	{*$(document).ready(function() {
		$("#ticketPeekTab2").load("{devblocks_url}ajax.php?c=setup&a=showExportTypeParams&type={$export_type->recipient_type}&id={$id}{/devblocks_url}");
		$('#export_type_recipient_type').change(function() {
			var sel = $(this).val();
			$("#ticketPeekTab2").load("{devblocks_url}ajax.php?c=setup&a=showExportTypeParams&type="+sel+"&id={$id}{/devblocks_url}");
		});
	});*}
</script>
