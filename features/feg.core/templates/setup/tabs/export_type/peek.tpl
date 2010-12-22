<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formExportPeek" name="formExportPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveExportPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">
<div id="peekTabs">
	<ul>
		<li><a href="#ticketPeekTab1">Properties</a></li>
		<li><a href="#ticketPeekTab2">Params</a></li>
	</ul>
		
<div id="ticketPeekTab1">
<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.export_type.new_source')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('common.disabled')|capitalize}: </td>
		<td width="100%">
			<select name="export_type_is_disabled">
				<option value="0" {if $export_type->is_disabled == 0}selected{/if}>{$translate->_('common.enable')|capitalize}</option>
				<option value="1" {if $export_type->is_disabled == 1}selected{/if}>{$translate->_('common.disable')|capitalize}</option>
			</select>
		</td>
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
</table>
<br>
</div>

<div id="ticketPeekTab2" style="display:none">
</div>

<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
{if $active_worker->is_superuser}
	<button type="button" onclick="if(confirm('Are you sure you want to delete this Export Type?')){literal}{{/literal}this.form.do_delete.value='1';genericPanel.dialog('close');genericAjaxPost('formExportPeek', 'view{$view_id}', '');{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>
{/if}
<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>

{* End div for the tab*}
</div>

<script type="text/javascript" language="JavaScript1.2">
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title',"Export Type Editor");
		$("#peekTabs").tabs();
		{*$("#ticketPeekContent").css('width','100%');*}
		$("#ticketPeekTab2").show();
		genericPanel.focus();
	} );
	$(document).ready(function() {
		$("#ticketPeekTab2").load("{devblocks_url}ajax.php?c=setup&a=showExportTypeParams&type={$export_type->recipient_type}&id={$id}{/devblocks_url}");
		$('#export_type_recipient_type').change(function() {
			var sel = $(this).val();
			$("#ticketPeekTab2").load("{devblocks_url}ajax.php?c=setup&a=showExportTypeParams&type="+sel+"&id={$id}{/devblocks_url}");
		});
	});
</script>
