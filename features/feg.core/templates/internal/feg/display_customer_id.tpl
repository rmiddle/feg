{if $result.$column == 0}
	{$translate->_('customer.display.invalid_customer')|capitalize}
{else}
	{$account = DAO_CustomerAccount::get($result.$column)}
	{$account->account_number}
{/if}
