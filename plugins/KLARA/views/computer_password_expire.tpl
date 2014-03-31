{assign var="paging_titles" value="KLARA, Access Information, Expire Computer Password"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

<h1>Expire Computer Password</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td>Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Computer:</td>
		<td>
			{if $computer->password->computer_id}
				{assign var="computer_id" value=$computer_password->computer_id}
				{$computers_list.$computer_id}
			{else}
				<b>[Network password]</b>
			{/if}
		</td>
	</tr>
	<tr>
		<td>Login:</td>
		<td>{$computer_password->login}</td>
	</tr>
	<tr>
		<td>Old password:</td>
		<td>{$computer_password->password}</td>
	</tr>
	<tr>
		<td>New password:</td>
		<td>
			<input type="text" name="new_password" value="" size="20"/>
			<br/>
			Leave empty if you don't want to define a new password.
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Expire"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
