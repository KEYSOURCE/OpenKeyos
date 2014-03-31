{assign var="paging_titles" value="KLARA, Manage Internet Providers, Add Internet Provider"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers"}
{include file="paging.html"}


<h1>Add Internet Provider</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Enter Internet Provider information</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name:</td>
		<td>
			<input type="text" name="provider[name]" value="{$provider->name}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Website:</td>
		<td>
			<input type="text" name="provider[website]" value="{$provider->website}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Address:</td>
		<td>
			<textarea name="provider[address]" rows="5" cols="40">{$provider->address|escape}</textarea>
		</td>
	</tr>
		
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
