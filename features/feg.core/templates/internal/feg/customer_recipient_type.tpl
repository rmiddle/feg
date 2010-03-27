<input type="hidden" name="oper" value="=">

<b>{$translate->_('search.value')|capitalize}:</b><br>
<blockquote style="margin:5px;">
	<label><input type="radio" name="bool" value="0" checked>{$translate->_('recipient.type.email')|capitalize}</label>
	<label><input type="radio" name="bool" value="1">{$translate->_('recipient.type.fax')|capitalize}</label>
	<label><input type="radio" name="bool" value="2">{$translate->_('recipient.type.snpp')|capitalize}</label>
	<label><input type="radio" name="bool" value="3">{$translate->_('recipient.type.web')|capitalize}</label>
	<br>
</blockquote>

