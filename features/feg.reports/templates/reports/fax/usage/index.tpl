<div id="headerSubMenu">
	<div style="padding-bottom:5px;"></div>
</div>

<h2>{$translate->_('reports.fax.daily.usage')}</h2>

<form action="{devblocks_url}c=reports&report=report.fax.daily.usage{/devblocks_url}" method="POST" id="frmRange" name="frmRange" style="margin-bottom:10px;">
<input type="hidden" name="c" value="reports">

<select name="field_id" onchange="this.form.btnSubmit.click();">
	{*{foreach from=$source_manifests item=mft}
		{foreach from=$custom_fields item=f key=field_idx}
			{if 'T' != $f->type && 0==strcasecmp($mft->id,$f->source_extension)}{* Ignore clobs *}
			<option value="{$field_idx}" {if $field_id==$field_idx}selected="selected"{/if}>{$mft->name}:{$f->name}</option>
			{/if}
		{/foreach}
	{/foreach}*}
			<option value="0" selected="selected">Default</option>
</select>

<button type="submit" id="btnSubmit">{$translate->_('common.refresh')|capitalize}</button>
<div id="divCal" style="display:none;position:absolute;z-index:1;"></div>
</form>
