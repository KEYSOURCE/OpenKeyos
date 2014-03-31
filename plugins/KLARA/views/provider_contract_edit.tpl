{assign var="provider_id" value=$provider->id}
{assign var="paging_titles" value="KLARA, Manage Internet Providers, Edit Internet Provider, Edit Contract"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_providers, /?cl=klara&op=provider_edit&id=$provider_id"}
{include file="paging.html"}


<h1>Edit Contract</h1>

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
		<td>Contract name:</td>
		<td>
			<input type="text" name="contract[name]" value="{$contract->name}" size="40"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="contract[comments]" rows="5" cols="40">{$contract->comments|escape}</textarea>
		</td>
	</tr>
		
</table>
<p/>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>
