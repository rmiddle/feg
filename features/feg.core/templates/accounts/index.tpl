<form>
<form method="post">
	<input type="hidden" name="c" value="account">
	<input type="hidden" name="a" value="createNewCustomer">
<button type="button" onclick="this.form.submit()"><span class="feg-sprite sprite-forbidden"></span> Add Customer</button>
	<button type="submit"><span class="feg-sprite sprite-check"></span>Add Account</button>
</form>

<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top" width="0%" nowrap="nowrap">
			{include file="file:$core_tpl/internal/views/criteria_list.tpl" divName="accountCriteriaDialog"}
			<div id="accountCriteriaDialog" style="visibility:visible;"></div>
		</td>
		<td valign="top" width="0%" nowrap="nowrap" style="padding-right:5px;"></td>
		<td valign="top" width="100%">
			<div id="view{$view->id}">{$view->render()}</div>
		</td>
	</tr>
</table>
