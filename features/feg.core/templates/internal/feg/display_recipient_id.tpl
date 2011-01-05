{if $result.$column == 0}
	{$translate->_('customer.display.invalid_recipient')|capitalize}
{else}
	{$recipient = DAO_CustomerRecipient::get($result.$column)}
	{if $recipient->type == 0}{$translate->_('recipient.type.email')|capitalize}
	{else if $recipient->type == 1}{$translate->_('recipient.type.fax')|capitalize}
	{else if $recipient->type == 2}{$translate->_('recipient.type.snpp')|capitalize}
	{else if $recipient->type == 255}{$translate->_('recipient.type.slave')|capitalize}
	{/if}-
	{if $recipient->type == 255}
		{$customer_account_master = DAO_CustomerAccount::get($recipient->address)}
		{if isset($customer_account_master)}
			{$customer_account_master->account_number} - {$customer_account_master->account_name}
		{else}
			{$translate->_('customer.display.invalid_recipient')|capitalize}
		{/if}
	{else}
		{$recipient->address|escape}
	{/if}
{/if}
