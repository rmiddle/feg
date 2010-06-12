{if $result.$column == 0}
	{$translate->_('customer.display.invalid_recipient')|capitalize}
{else}
	{$recipient = DAO_CustomerRecipient::get($result.$column)}
	{if $recipient->type == 0}{$translate->_('recipient.type.email')|capitalize}
	{else if $recipient->type == 1}{$translate->_('recipient.type.fax')|capitalize}
	{else if $recipient->type == 2}{$translate->_('recipient.type.snpp')|capitalize}
	{/if}-
	{$recipient->address}
{/if}
