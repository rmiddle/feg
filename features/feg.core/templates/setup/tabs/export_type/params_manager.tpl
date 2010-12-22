{foreach from=$export_type->params item=param key=param_id}
	<tr>
		<td width="0%" nowrap="nowrap" align="right"><b>{$parm_id}</b>: </td>
		<td width="100%"><input type="text" name="export_type_params_{$param_id}" value="{$param|escape}" style="width:98%;"></td>
	</tr>
{/foreach}
