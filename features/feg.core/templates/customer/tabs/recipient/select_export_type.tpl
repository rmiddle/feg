<select name="recipient_export_type">
	{foreach from=$export_type item=export name=export_id}
		{if $type == $export->recipient_type}
			<option value="{$export->id}" {if $customer_recipient->export_type == $export->id}selected{/if}>{$export->name}</option>
		{/if}
	{/foreach}
</select>