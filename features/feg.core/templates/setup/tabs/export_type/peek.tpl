<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formImportPeek" name="formImportPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveImportPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">

{$imports = DAO_ImportSource::get($id)}
<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.import_source.new_source')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('common.disabled')|capitalize}: </td>
		<td width="100%">
			<select name="imports_is_disabled">
				<option value="0" {if $imports->is_disabled == 0}selected{/if}>{$translate->_('common.enable')|capitalize}</option>
				<option value="1" {if $imports->is_disabled == 1}selected{/if}>{$translate->_('common.disable')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('feg.import_source.name')}</b>: </td>
		<td width="100%"><input type="text" name="import_name" value="{$imports->name|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">{$translate->_('feg.import_source.peek.type')|capitalize}: </td>
		<td width="100%">
			<select name="import_type">
				<option value="0" {if $imports->type == '0'}selected{/if}>{$translate->_('feg.import_source.peek.type.ixo')|capitalize}</option>
				<option value="1" {if $imports->type == '1'}selected{/if}>{$translate->_('feg.import_source.peek.type.common')|capitalize}</option>
				<option value="2" {if $imports->type == '2'}selected{/if}>{$translate->_('feg.import_source.peek.type.pi')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('feg.import_source.path')}</b>: </td>
		<td width="100%"><input type="text" name="import_path" value="{$imports->path|escape}" style="width:98%;"></td>
	</tr>
</table>
<br>
<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formImportPeek', 'view{$view_id}', '');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
{if $active_worker->is_superuser}
	<button type="button" onclick="if(confirm('Are you sure you want to delete this Import Source?')){literal}{{/literal}this.form.do_delete.value='1';genericPanel.dialog('close');genericAjaxPost('formImportPeek', 'view{$view_id}', '');{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>
{/if}
<button type="button" onclick="genericPanel.dialog('close');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
<br>
</form>

<script type="text/javascript" language="JavaScript1.2">
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Import'); 
	} );
</script>
