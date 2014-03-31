{assign var="paging_titles" value="KLARA, Access Information, Add Computer Password"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

<h1>Add Computer Password</h1>

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
			<select name="computer_password[computer_id]">
				<option value="0">[Network password]</option>
				{html_options options=$computers_list selected=$computer_password->computer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Login:</td>
		<td>
			<input type="text" name="computer_password[login]" value="{$computer_password->login}" size="20"/>
		</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td>
			<input type="text" name="computer_password[password]" value="{$computer_password->password}" size="20"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="computer_password[comments]" rows="4" cols="60">{$computer_password->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
