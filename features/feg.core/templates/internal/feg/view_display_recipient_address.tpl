if $customer_recipient_address == 255}
	{$customer_account_master = DAO_CustomerAccount::get($customer_recipient->address)}
	{if isset($customer_account_master)}
		{$customer_account_master->account_number} - {$customer_account_master->account_name}
	{else}
		{$translate->_('customer.display.invalid_recipient')|capitalize}
	{/if}
{else}
	{$customer_recipient->address|escape}
{/if}