{assign var=results value=$view->getData()}
{assign var=total value=$results[1]}
{assign var=data value=$results[0]}

<table cellpadding="1" cellspacing="0" border="0" width="100%" class="worklistBody">
<tr nowrap="nowrap" style="background-color:rgb(232,242,254);border-color:rgb(121,183,231);"><td><span class="title"><h2>{$view->name}</h2></span></td></tr>
</table>

<form id="viewForm{$view->id}" name="viewForm{$view->id}" action="{devblocks_url}{/devblocks_url}" method="post" onsubmit="return false;">
<input type="hidden" name="view_id" value="{$view->id}">
<input type="hidden" name="c" value="tickets">
<input type="hidden" name="a" value="">
<table cellpadding="1" cellspacing="0" border="0" width="100%" class="worklistBody">
	{* Column Headers *}
	<tr>
		{foreach from=$view->view_columns item=header name=headers}
		{if $header=='mr_id' || $header=='mr_account_id' || $header=='mr_recipient_id' || $header=='mr_message_id'}
			{* start table header, insert column title and link *}
			<th nowrap="nowrap" style="background-color:rgb(232,242,254);border-color:rgb(121,183,231);">
			<a href="javascript:;" style="color:rgb(74,110,158);" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewSortBy&id={$view->id}&sortBy={$header}');">{$view_fields.$header->db_label|capitalize}</a>
			
			{* add arrow if sorting by this column, finish table header tag *}
			{if $header==$view->renderSortBy}
				{if $view->renderSortAsc}
					<span class="feg-sprite sprite-sort_ascending"></span>
				{else}
					<span class="feg-sprite sprite-sort_descending"></span>
				{/if}
			{/if}
			</th>
		{/if}
		{/foreach}
	</tr>

	{* Column Data *}
	{foreach from=$data item=result key=idx name=results}

	{assign var=rowIdPrefix value="row_"|cat:$view->id|cat:"_"|cat:$result.mr_id}
	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowBg value="even"}
	{else}
		{assign var=tableRowBg value="odd"}
	{/if}
	
		<tr class="{$tableRowClass}" id="{$rowIdPrefix}_s" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="if(getEventTarget(event)=='TD') checkAll('{$rowIdPrefix}');">
		{foreach from=$view->view_columns item=column name=columns}
			{if $column=="mr_id"}
				<td><a href="javascript:;" onclick="genericAjaxPanel('c=stats&a=showMessageRecipientFailurePeek&id={$result.mr_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{$result.$column}&nbsp;</a></td>
			{elseif $column=="mr_account_id"}
				<td><a href="javascript:;" onclick="genericAjaxPanel('c=stats&a=showMessageRecipientFailurePeek&id={$result.mr_id}&view_id={$view->id|escape:'url'}',null,false,'550');">
					{if $result.mr_account_id == 0}
						{$translate->_('customer.display.invalid_customer')|capitalize}
					{else}
						<a href="{devblocks_url}{/devblocks_url}customer/{$result.mr_account_id}/property">
							{include file="file:$core_tpl/internal/feg/display_customer_id.tpl"}&nbsp;
						</a>
					{/if}
				&nbsp;</a></td>
			{elseif $column=="mr_recipient_id"}
				<td>
					<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recipient&action=showRecipientPeek&id={$result.mr_recipient_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{$result.mr_recipient_id}</a>&nbsp;
				</td>
			{elseif $column=="mr_message_id"}
				<td>
					<a href="javascript:;" onclick="genericAjaxPanel('c=customer&a=handleTabAction&tab=feg.customer.tab.recent.messages&action=showMessagePeek&id={$result.mr_message_id}&customer_id={$result.mr_account_id}&view_id={$view->id|escape:'url'}',null,false,'550');">{$result.mr_message_id}&nbsp;</a>
				</td>
			{/if}
		{/foreach}
		</tr>
	{/foreach}
	
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%" id="{$view->id}_actions">
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
