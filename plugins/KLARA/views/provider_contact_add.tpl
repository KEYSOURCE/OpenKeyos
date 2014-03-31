{assign var="provider_id" value=$provider->id}
{assign var="paging_titles" value="KLARA, Manage Internet Providers, Edit Internet Provider, Add Contact"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers, /?cl=klara&op=provider_edit&id=$provider_id"}
{include file="paging.html"}

<h1>Add Contact</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td width="20%">Provider:</td>
		<td>{$provider->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td>First name/Name:</td>
		<td>
			<input type="text" name="contact[fname]" value="{$contact->fname}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Last name:</td>
		<td>
			<input type="text" name="contact[lname]" value="{$contact->lname}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td>
			<input type="text" name="contact[email]" value="{$contact->email}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="contact[comments]" rows="5" cols="40">{$contact->comments|escape}</textarea>
		</td>
	</tr>
		
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
