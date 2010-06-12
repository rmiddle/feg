{if $result.$column == 0}{$translate->_('feg.core.send_status.new')|capitalize}
{else if $result.$column == 1}{$translate->_('feg.core.send_status.fail')|capitalize}
{else if $result.$column == 2}{$translate->_('feg.core.send_status.successful')|capitalize}
{/if}

