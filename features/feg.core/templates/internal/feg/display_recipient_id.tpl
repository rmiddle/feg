{if $result.$column == 0}
	{$translate->_('customer.display.invalid_recipient')|capitalize}
{else}
	{$recipient = DAO_CustomerRecipient::get($result.$column)}
	{$recipient->address}
{/if}
