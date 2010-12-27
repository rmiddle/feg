{assign var=results value=$view->getData()}
{assign var=total value=$results[1]}
{assign var=data value=$results[0]}
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="worklist">
	<tr>
		<td nowrap="nowrap"><span class="title">{$view->name}</span></td>
		<td nowrap="nowrap" align="right">
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');">{$translate->_('common.refresh')|lower}</a>
		</td>
	</tr>
</table>

<form id="customize{$view->id}" name="customize{$view->id}" action="#" onsubmit="return false;" style="display:none;"></form>
<form id="viewForm{$view->id}" name="viewForm{$view->id}">
<input type="hidden" name="id" value="{$view->id}">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="worklistBody">

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
					<span class="cerb-sprite sprite-sort_ascending"></span>
				{else}
					<span class="cerb-sprite sprite-sort_descending"></span>
				{/if}
			{/if}
			</th>
		{/foreach}
	</tr>

	{* Column Data *}
	{foreach from=$data item=result key=idx name=results}

	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowClass value="even"}
	{else}
		{assign var=tableRowClass value="odd"}
	{/if}
	<tbody onmouseover="$(this).find('tr').addClass('hover');" onmouseout="$(this).find('tr').removeClass('hover');" onclick="if(getEventTarget(event)=='TD') { var $chk=$(this).find('input:checkbox:first');if(!$chk) return;$chk.attr('checked', !$chk.is(':checked')); } ">
		<tr class="{$tableRowClass}">
			<td align="center" rowspan="1"><input type="checkbox" name="row_id[]" value="{$result.l_id}"></td>
		{foreach from=$view->view_columns item=column name=columns}
			{if $column=="l_id"}
				<td>{$result.l_id}&nbsp;</td>
			{elseif $column=="l_account_id"}
				<td>
					{if $result.l_account_id==0}
						{$translate->_('customer.display.auto')|capitalize}
					{else}
						<a href="{devblocks_url}{/devblocks_url}customer/{$result.l_account_id}/property">
							{include file="file:$core_tpl/internal/feg/display_customer_id.tpl"}&nbsp;
						</a>
					{/if}
				</td>
			{elseif $column=="l_recipient_id"}
				<td>
					{if $result.l_recipient_id==0}
						{$translate->_('customer.display.auto')|capitalize}
					{else}
						<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$result.l_recipient_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{include file="file:$core_tpl/internal/feg/display_recipient_id.tpl"}</a>&nbsp;
					{/if}
				</td>
			{elseif $column=="l_message_id"}
				<td>
					{if $result.l_message_id==0}
						{$translate->_('customer.display.auto')|capitalize}
					{else}
						<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recent.messages&action=showMessagePeek&id={$result.l_message_id}&customer_id={$result.l_account_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{$result.l_message_id}&nbsp;</a>
					{/if}
				</td>
			{elseif $column=="l_message_recipient_id"}
				<td>
					{if $result.l_message_recipient_id==0}
						{$translate->_('customer.display.auto')|capitalize}
					{else}
						<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recent.messages&action=showMessageRecipientPeek&id={l_message_recipient_id}&customer_id={$result.l_account_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{l_message_recipient_id}&nbsp;</a>
					{/if}				
				</td>
			{elseif $column=="l_worker_id"}
				<td>
				{assign var=log_worker_id value=$result.l_worker_id}
				{if isset($workers.$log_worker_id)}{$workers.$log_worker_id->getName()}{else}(auto){/if}&nbsp;
				</td>
			{elseif $column=="l_change_date"}
			<td><abbr title="{$result.l_change_date|devblocks_date}">{$result.l_change_date|devblocks_prettytime}</abbr>&nbsp;</td>
			{elseif $column=="l_change_field"}
				<td>{$translate->_($result.l_change_field)}&nbsp;</td>
			{elseif $column=="l_change_value"}
				<td>
					{if $result.l_change_field=='auditlog.cr.account_id'}
						{include file="file:$core_tpl/internal/feg/display_customer_id.tpl"}
					{elseif $result.l_change_field=='auditlog.ca.is_disabled' || $result.l_change_field=='auditlog.cr.is_disabled'}
						{if $result.l_change_value==0}
							{$translate->_('common.enable')|capitalize}
						{else}
							{$translate->_('common.disable')|capitalize}
						{/if}
					{elseif $result.l_change_field=='auditlog.cr.export_type'}
						{$export = DAO_ExportType::get($result.l_change_value)}
						{$export->name}
					{else}
						{$result.l_change_value}
					{/if}
				&nbsp;</td>
			{else}
				<td>{$result.$column}&nbsp;</td>
			{/if}
		{/foreach}
		</tr>
	</tbody>
	{/foreach}
	
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%" id="{$view->id}_actions">
	{if $total}
	<tr>
		<td colspan="2">
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
