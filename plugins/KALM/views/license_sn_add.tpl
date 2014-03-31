{assign var="license_id" value=$license->id}
{assign var="paging_titles" value="KALM, Manage Licenses, Edit License, Add Serial Number"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses, /?cl=kalm&op=license_edit&id=$license_id"}
{include file="paging.html"}

<h1>Add Serial Number</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}


<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Serial number details</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Serial number:</td>
		<td>
			<input type="text" name="sn[sn]" value="{$sn->sn}" size="60" />
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="sn[comments]" rows="5" cols="60">{$sn->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>


<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Cancel" />

</form>