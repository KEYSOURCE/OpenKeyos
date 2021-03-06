{assign var="paging_titles" value="Configure Support Emails, Add"}
{assign var="paging_urls" value="/krifs/manage_support_emails"}
{include file="paging.html"}

<h1>Add Support Email</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
            <td colspan="2">Details</td>
	</tr>
	</thead>

	<tr>
		<td width="20%">Server:</td>
		<td><input type="text" name="imapsettings[server]" value="{$imapsettings->server}" /></td>
	</tr>
	<tr>
		<td>Port:</td>
		<td>
			<input type="text" name="imapsettings[port]" value="{$imapsettings->port}" />
		</td>
	</tr>
        <tr>
		<td>Username:</td>
		<td>
			<input type="text" name="imapsettings[username]" value="{$imapsettings->username}" />
		</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td>
			<input type="text" name="imapsettings[password]" value="{$imapsettings->password}" />
		</td>
	</tr>
        <tr>
		<td>Mailbox:</td>
		<td>
			<input type="text" name="imapsettings[mailbox]" value="{$imapsettings->mailbox}" /> (e.g.: INBOX)
		</td>
	</tr>
        <tr>
		<td>Encrypt:</td>
		<td>
                    <select name="imapsettings[encrypt]">
                        <option value="">----</option>
                        <option value="ssl">SSL</option>
                        <option value="tls">TLS</option>
                        <option value="notls">NO-TLS</option>
                    </select>
		</td>
	</tr>
        <tr>
		<td>Validate Certificate:</td>
		<td>
                    <select name="imapsettings[validate_cert]">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
		</td>
	</tr>
        <tr>
            <td>Select user to assign tickets</td>
            <td>
                <select name="imapsettings[assigned_user_id]">
                    {foreach from=$users key=id item=name}
                    <option value="{$id}">{$name}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
</table>
<p/>


<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Cancel" />

</form>
