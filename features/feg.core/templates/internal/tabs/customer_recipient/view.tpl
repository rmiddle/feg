{assign var=results value=$view->getData()}
{assign var=total value=$results[1]}
{assign var=data value=$results[0]}

<table cellpadding="0" cellspacing="0" border="0" class="worklist" width="100%">
	<tr>
		<td nowrap="nowrap"><span class="title">{$view->name}</span></td>
		<td nowrap="nowrap" align="right">
			<a href="javascript:;" onclick="genericAjaxGet('customize{$view->id}','c=internal&a=viewCustomize&id={$view->id}');toggleDiv('customize{$view->id}','block');">{$translate->_('common.customize')|lower}</a>
			 | <a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');">{$translate->_('common.refresh')|lower}</a>
		</td>
	</tr>
</table>

<div id="{$view->id}_tips" class="block" style="display:none;margin:10px;padding:5px;">Loading...</div>
<form id="customize{$view->id}" name="customize{$view->id}" action="#" onsubmit="return false;" style="display:none;"></form>
<form id="viewForm{$view->id}" name="viewForm{$view->id}" action="#">
<input type="hidden" name="view_id" value="{$view->id}">
<input type="hidden" name="c" value="setup">
<input type="hidden" name="a" value="">
<table cellpadding="1" cellspacing="0" border="0" width="100%" class="worklistBody">

	{* Column Headers *}
	<tr>
		<th style="text-align:center"><input type="checkbox" onclick="checkAll('view{$view->id}',this.checked);"></th>
		{foreach from=$view->view_columns item=header name=headers}
			{* start table header, insert column title and link *}
			<th nowrap="nowrap">
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewSortBy&id={$view->id}&sortBy={$header}');">{$view_fields.$header->db_label|capitalize}</a>
			
			{* add arrow if sorting by this column, finish table header tag *}
			{if $header==$view->renderSortBy}
				{if $view->renderSortAsc}
					<img src="{devblocks_url}c=resource&p=feg.core&f=images/sort_ascending.png{/devblocks_url}" align="absmiddle">
				{else}
					<img src="{devblocks_url}c=resource&p=feg.core&f=images/sort_descending.png{/devblocks_url}" align="absmiddle">
				{/if}
			{/if}
			</th>
		{/foreach}
	</tr>

	{* Column Data *}
	{foreach from=$data item=result key=idx name=results}

	{assign var=rowIdPrefix value="row_"|cat:$view->id|cat:"_"|cat:$result.cr_id}
	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowBg value="even"}
	{else}
		{assign var=tableRowBg value="odd"}
	{/if}
	
		<tr class="{$tableRowBg}" id="{$rowIdPrefix}" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="if(getEventTarget(event)=='TD') checkAll('{$rowIdPrefix}');">
			<td align="center"><input type="checkbox" name="row_id[]" value="{$result.cr_id}"></td>
		{foreach from=$view->view_columns item=column name=columns}
			{if substr($column,0,3)=="cf_"}
				{include file="file:$core_tpl/internal/custom_fields/view/cell_renderer.tpl"}
			{elseif $column=="cr_id" || $column=="cr_address" || $column=="cr_address_to" || $column=="cr_subject"}
				<td>
					<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$result.cr_id}&view_id={$view->id|escape:'url'}',null,false,'650');">
					{$result.$column}&nbsp;</a></td>
			{elseif $column=="cr_is_disabled"}
				<td>{if $result.cr_is_disabled}{$translate->_('common.disable')|capitalize}{else}{$translate->_('common.enable')|capitalize}{/if}</td>
			{elseif $column=="cr_type"}
				<td>
					<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$result.cr_id}&view_id={$view->id|escape:'url'}',null,false,'650');">
					{include file="file:$core_tpl/internal/feg/display_recipient_type.tpl"}</a>
					&nbsp;
				</td>
			{elseif $column=="cr_export_type"}
				<td>
					{$export = DAO_ExportType::get($result.cr_export_type)}
					<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$result.cr_id}&view_id={$view->id|escape:'url'}',null,false,'650');">
					{$export->name}</a>&nbsp;
				</td>
			{elseif $column=="cr_account_id"}
				<td>{include file="file:$core_tpl/internal/feg/display_customer_id.tpl"}&nbsp;</td>
			{else}
				<td>{$result.$column}&nbsp;</td>
			{/if}
		{/foreach}
		</tr>
	{/foreach}
	
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%" id="{$view->id}_actions">
	{if $total}
	<tr>
		<td colspan="2">
			{if $active_worker && $active_worker->is_superuser}
				<button type="button" onclick="genericAjaxPanel('c=setup&a=showRecipientBulkPanel&view_id={$view->id}&ids=' + Devblocks.getFormEnabledCheckboxValues('viewForm{$view->id}','row_id[]'),null,false,'500');"><img src="{devblocks_url}c=resource&p=feg.core&f=images/folder_gear.gif{/devblocks_url}" align="top"> bulk update</button>
			{/if}
		</td>
	</tr>
	{/if}
	<tr>
		<td align="right" valign="top" nowrap="nowrap">
			{math assign=fromRow equation="(x*y)+1" x=$view->renderPage y=$view->renderLimit}
			{math assign=toRow equation="(x-1)+y" x=$fromRow y=$view->renderLimit}
			{math assign=nextPage equation="x+1" x=$view->renderPage}
			{math assign=prevPage equation="x-1" x=$view->renderPage}
			{math assign=lastPage equation="ceil(x/y)-1" x=$total y=$view->renderLimit}
			
			{* Sanity checks *}
			{if $toRow > $total}{assign var=toRow value=$total}{/if}
			{if $fromRow > $toRow}{assign var=fromRow value=$toRow}{/if}
			
			{if $view->renderPage > 0}
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page=0');">&lt;&lt;</a>
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$prevPage}');">&lt;{$translate->_('common.previous_short')|capitalize}</a>
			{/if}
			({'views.showing_from_to'|devblocks_translate:$fromRow:$toRow:$total})
			{if $toRow < $total}
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$nextPage}');">{$translate->_('common.next')|capitalize}&gt;</a>
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$lastPage}');">&gt;&gt;</a>
			{/if}
		</td>
	</tr>
</table>
</form>
<br>
