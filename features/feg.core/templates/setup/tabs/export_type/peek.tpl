<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formExportPeek" name="formExportPeek" onsubmit="return false;">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveExportPeek">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="export_type_is_disabled" value="{$export_type->is_disabled}">

<table id="table_export_type" cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.export_type.new_source')|capitalize}{/if}</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('feg.export_type.name')}</b>: </td>
		<td width="100%"><input type="text" name="export_type_name" value="{$export_type->name|escape}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$translate->_('feg.export_type.peek.recipient_type')|capitalize}</b>: </td>
		<td width="100%">
			<select name="export_type_recipient_type" id="export_type_recipient_type">
				<option value="0" {if $export_type->recipient_type == '0'}selected{/if}>{$translate->_('recipient.type.email')|capitalize}</option>
				<option value="1" {if $export_type->recipient_type == '1'}selected{/if}>{$translate->_('recipient.type.fax')|capitalize}</option>
				<option value="2" {if $export_type->recipient_type == '2'}selected{/if}>{$translate->_('recipient.type.snpp')|capitalize}</option>
				<option value="255" {if $export_type->recipient_type == '255'}selected{/if}>{$translate->_('recipient.type.slave')|capitalize}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">&nbsp;</td>
		<td width="100%">&nbsp;</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b></b>{$translate->_('feg.export_type.add_filter')|capitalize}: </td>
		<td width="100%">
			<select name="export_type_params_add" id="export_type_params_add">
				{*<option value="">{$translate->_('feg.export_type.peek.add_param')|capitalize}</option>
				{foreach from=$export_type_params item=export_type_param key=export_type_param_id}
					{if $export_type->recipient_type == $export_type_param->recipient_type}
						<option value="{$export_type_param->id}">{$export_type_param->name}</option>
					{/if}
				{/foreach}*}
			</select>
		</td>
	</tr>
	{foreach from=$export_type->params item=param key=param_id}
		<tr>
			<td width="0%" nowrap="nowrap" align="right">
				<b>{$export_type_params.$param_id->name}</b>: 
				<input type="hidden" name="params_ids[]" value="{$param_id}">
				<button type="button" class="delete" onclick=""><span class="feg-sprite sprite-delete"></span></button>
			</td>
			<td width="100%">
				{if ($export_type_params[$param_id]->type == 1)}
					<select name="export_type_params_{$param_id}" id="export_type_params_{$param_id}">
						<option value="1" {if $param == '1'}selected{/if}>{$translate->_('common.yes')|capitalize}</option>
						<option value="0" {if $param == '0'}selected{/if}>{$translate->_('common.no')|capitalize}</option>
					</select>
				{else if ($export_type_params[$param_id]->type == 2)}
					<input type="text" name="export_type_params_{$param_id}" value="{$param|escape}" style="width:98%;">
				{/if}
			</td>
		</tr>
	{/foreach}
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
	$(genericPanel).one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Export Type Editor'); 
	} );
	$(document).ready(function() {
		$('#table_export_type td :button.delete').click(function(){
			$(this).parent().parent().remove();
		});
		$.getJSON("{devblocks_url}ajax.php?c=setup&a=showExportPeekTypeParm&type={$export_type->recipient_type}{/devblocks_url}", function(data) {
			var select = $('#export_type_params_add');
			var options = select.attr('options');
			$('option', select).remove();
			$('#export_type_params_add').append('<option value="" selected="selected">Select to Add option</option>');
			
			$.each(data, function(index, array) {
				options[options.length] = new Option(array['name'], index);
			});
		});
		$('#export_type_recipient_type').change(function() {
			var sel = $(this).val();
			$.getJSON("{devblocks_url}ajax.php?c=setup&a=showExportPeekTypeParm&type="+sel+"{/devblocks_url}", function(data) {
				var select = $('#export_type_params_add');
				var options = select.attr('options');
				$('option', select).remove();
				$('#export_type_params_add').append('<option value="" selected="selected">Select to Add option</option>');
			
				$.each(data, function(index, array) {
					options[options.length] = new Option(array['name'], index);
				});
			});
		});
		
		$('#export_type_params_add').change(function() {
			var sel_id = $(this).val();
			$.getJSON("{devblocks_url}ajax.php?c=setup&a=saveExportPeekTypeParmAdd&id={$export_type->id}&add_id="+sel_id+"{/devblocks_url}", function(data) {
				var table = $('#table_export_type');
				// Number of td's in the last table row
				var tds = '<tr>';
				tds += '<td width="0%" nowrap="nowrap" align="right"><b>';
				tds += data.name;
				tds += '</b>: ';
				tds += '<input type="hidden" name="params_ids[]" value="';
				tds += data.id;
				tds += '">';
				tds += '<button type="button" class="delete" onclick="';
				tds += '"><span class="feg-sprite sprite-delete"></span></button>';
				tds += '</td>';
				tds += '<td width="100%">';
				if(data.type == 2) {
					tds += '<input type="text" name="export_type_params_';
					tds += data.id;
					tds += '" value="';
					tds += data.default;
					tds += '" style="width:98%;">';			
				}
				if(data.type == 1) {
					tds += '<select name="export_type_params_';
					tds += data.id;
					tds += '">';
					tds += '<option value="1" ';
					if(data.default == 1) {
						tds += 'selected ';
					}
					tds += '>Yes</option>';
					tds += '<option value="0" ';
					if(data.default == 0) {
						tds += 'selected ';
					}
					tds += '>No</option>';
					tds += '</select>';
				}
				tds += '</td>';
				tds += '</tr>';
				if($('tbody', table).length > 0){
					$('tbody', table).append(tds);
				}else {
					$(table).append(tds);
				}
				$('#table_export_type td :button.delete').click(function(){
					$(this).parent().parent().remove();
				});
			});
		});
	});
	
</script>
