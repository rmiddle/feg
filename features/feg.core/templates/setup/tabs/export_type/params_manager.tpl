<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td nowrap="nowrap" align="right">ID: </td>
		<td>{if $id}{$id}{else}{$translate->_('feg.export_type.new_source')|capitalize}{/if}</td>
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
							<option value="{$export_type_param->id}">{$export_type_param->name} - {$export_type_param->options['default']}</option>
						{/if}
					{/foreach}
				</select>
		</td>
	</tr>
</table>