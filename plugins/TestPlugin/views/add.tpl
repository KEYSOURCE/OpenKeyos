{assign var="paging_titles" value="TestPlugin, Add item"}
{assign var="paging_urls" value="/?cl=test_plugin"}
{*include file='paging.tpl'*}

<h1>Test Plugin List all items</h1>
<p class='error'>{$error_msg}</p>
<form name="frm_testPlugin_add" method="POST" action="">
    {$form_redir}
    <table>
	<tr>
		<td>Name:</td>
		<td><input type="text" name="item[name]" value="" /></td>
	</tr>
	<tr>
		<td>Value:</td>
		<td><input type="text" name="item[value]" value="" /></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>