<form id="frmConfigPlugins" action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="saveTabPlugins">

<a href="javascript:;" onclick="checkAll('frmConfigPlugins', true);">select all</a>

<a href="javascript:;" onclick="checkAll('frmConfigPlugins', false)">select none</a>
<br>
<br>

{foreach from=$plugins item=plugin}
	<div style="margin-top:5px;padding-top:5px;border-top:1px dashed rgb(230,230,230);background-color:rgb(255,255,255);{if $plugin->enabled}{else}margin-left:10px;{/if}" id="config_plugin_{$plugin->id}">
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td valign="middle" align="left" width="1%" nowrap="nowrap">
					<img src="{devblocks_url}{if !empty($plugin->manifest_cache.plugin_image)}c=resource&p={$plugin->id}&f={$plugin->manifest_cache.plugin_image}{else}c=resource&p=cerberusweb.core&f=images/wgm/plugin_code_gray.gif{/if}{/devblocks_url}" width="100" height="100" border="0" style="border:1px solid rgb(150,150,150);" onclick="checkAll('config_plugin_{$plugin->id}');">
				</td>
				<td width="99%" valign="top" align="left" style="padding-left:5px;">
					<input type="checkbox" name="plugins_enabled[]" value="{$plugin->id}" {if $plugin->enabled}checked{/if}>
					<h3 style="display:inline;" onclick="checkAll('config_plugin_{$plugin->id}');"><span style="{if !$plugin->enabled}color:rgb(120,120,120);background-color:rgb(230,230,230);{else}color:rgb(50,120,50);background-color:rgb(219,255,190);{/if}">{$plugin->name}</span></h3>
					&nbsp; 
					<!-- (Revision: {$plugin->revision}) -->
					{if !empty($plugin->link)}<a href="{$plugin->link}" target="_blank">more info</a>{/if}
					<br>
					by <span style="font-weight:normal;color:rgb(120,120,120);">{$plugin->author}</span>
					<div style="padding:5px;">
						{$plugin->description}
					</div>
				</td>
			</tr>
		</table>
	</div>
{foreachelse}
	<b>No extensions installed.</b><br>
{/foreach}

<br>

<button type="submit"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
</form>

