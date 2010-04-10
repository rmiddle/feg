<input type="hidden" name="oper" value="=">

<b>{$translate->_('search.value')|capitalize}:</b><br>
<blockquote style="margin:5px;">
	<label><input type="radio" name="value" value="0" checked>{$translate->_('recipient.type.email')|capitalize}</label>
	<label><input type="radio" name="value" value="1">{$translate->_('recipient.type.fax')|capitalize}</label>
	<label><input type="radio" name="value" value="2">{$translate->_('recipient.type.snpp')|capitalize}</label>
	<br>
</blockquote>

