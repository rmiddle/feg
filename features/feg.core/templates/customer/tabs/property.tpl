<div id="customerData">
			<form action="{devblocks_url}{/devblocks_url}" method="post">
			<input type="hidden" name="c" value="customer">
			<input type="hidden" name="a" value="saveTab">
			<input type="hidden" name="tab" value="feg.customer.tab.recipient">
			<input type="hidden" name="customer_id" value="{$customer_id}">
			<h2>Customer Account Property's</h2>

			<button type="submit"><img src="{devblocks_url}c=resource&p=feg.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
			</form>
</div>

<br>
