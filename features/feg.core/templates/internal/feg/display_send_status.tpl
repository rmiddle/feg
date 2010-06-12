{if $result.$column == 0}{$translate->_('recipient.type.email')|capitalize}
{else if $result.$column == 1}{$translate->_('recipient.type.fax')|capitalize}
{else if $result.$column == 2}{$translate->_('recipient.type.snpp')|capitalize}
{/if}

