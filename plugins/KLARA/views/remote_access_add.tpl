{assign var="paging_titles" value="KLARA, Manage Access Phones, Add Remote Access Info"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

<h1>Add Remote Access Info</h1>

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
		<td width="20%">Public IP address:</td>
		<td>
			<input type="text" name="remote_access[public_ip]" value="{$remote_access->public_ip}" size="20"/>
		</td>
	</tr>
	<tr>
		<td>Has port forwarding:</td>
		<td>
			<select name="remote_access[has_port_forwarding]">
				<option value="0">No</option>
				<option value="1" {if $remote_access->has_port_forwarding}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Port forwarding port:</td>
		<td>
			<input type="text" name="remote_access[pf_port]" value="{$remote_access->pf_port}" size="10"/>
		</td>
	</tr>
	<tr>
		<td>Port forwarding login:</td>
		<td>
			<input type="text" name="remote_access[pf_login]" value="{$remote_access->pf_login}" size="20"/>
		</td>
	</tr>
	<tr>
		<td>Port forwarding password:</td>
		<td>
			<input type="text" name="remote_access[pf_password]" value="{$remote_access->pf_password}" size="20"/>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
