<h2>Congratulations!  Setup Complete.</h2>

<form action="index.php" method="POST">
<input type="hidden" name="step" value="{$smarty.const.STEP_FINISHED}">
<input type="hidden" name="form_submit" value="1">

<H3>Your new copy of Usermeet is ready for business!</H3>
<a href="{devblocks_url}c=login{/devblocks_url}">Take me there!</a><br>
<br>

<H3>Welcome to the Usermeet community!</H3>

Usermeet is ...<br>
<br>
The best place to become familiar with the concepts used in Usermeet 
is the <a href="http://wiki.usermeet.com/wiki/Main_Page" target="_blank">online documentation</a>. 
This area is dedicated to creating and maintaining tutorials, feature guides, and best practices.<br>
<br>

<div class="error">
	You should delete the 'install' directory now.
</div>

<br>

</form>