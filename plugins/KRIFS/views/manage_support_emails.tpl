{assign var="paging_titles" value="Configure Support Email"}
{assign var="paging_urls" value=""}
{include file="paging.html"}

<h1>Configure Support Emails</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<a href="{'krifs'|get_link:'add_support_email'}">Add support email &#0187;</a>
<p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Server</td>
		<td>Port</td>
		<td>Mailbox</td>
                <td>Username</td>
                <td>Password</td>
		<td>Encryption</td>
                <td>Validate certificate</td>
                <td>&nbsp;</td>
	</tr>
	</thead>

	{foreach from=$ims item=s}
		<tr>
            {assign var="p" value="id:"|cat:$$s->id->id}
			<td><a href="{'krifs'|get_link:'edit_support_email':$p:'template'}">{$s->id}</a></td>
			<td><a href="{'krifs'|get_link:'edit_support_email':$p:'template'}">{$s->server}</a></td>
			<td>
				{$s->port}
			</td>
			<td>
				{$s->mailbox}
			</td>
			<td>
				{$s->username}
			</td>
			<td>
				{$s->password}
			</td>
			<td>
				{$s->encrypt|strtoupper}
			</td>
			<td>
				{if $s->validate_cert}Yes{else}No{/if}
			</td>
			<td>
                <a href="{'krifs'|get_link:'delete_support_email':$p:'template'}" onclick="return confirm('Are you sure you want to delete {$s->server}:{$s->port}?');">Delete</a>
			</td>
		</tr>
	{/foreach}
</table>
<p>