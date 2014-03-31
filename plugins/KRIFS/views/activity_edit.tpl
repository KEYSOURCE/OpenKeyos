{assign var="paging_titles" value="KRIFS, Configure Activities, Edit Activity"}
{assign var="paging_urls" value="/krifs, /krifs/manage_activities"}
{include file="paging.html"}

<h1>Edit Activity</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Activity definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Name:</td>
		<td><input type="text" name="activity[name]" value="{$activity->name|escape}" /></td>
	</tr>
	<tr>
		<td>ERP ID:</td>
		<td>{$activity->erp_id}</td>
	</tr>
	<tr>
		<td>ERP Name:</td>
		<td>{$activity->erp_name|escape}</td>
	<tr>
		<td>Category:</td>
		<td>
			<select name="activity[category_id]">
				<option value="0">[None]</option>
				{html_options options=$categories_list selected=$activity->category_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Is travel?</td>
		<td>
			<select name="activity[is_travel]">
				<option value="0">No</option>
				<option value="1" {if $activity->is_travel}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>
