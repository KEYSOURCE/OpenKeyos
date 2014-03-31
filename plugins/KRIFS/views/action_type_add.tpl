{assign var="paging_titles" value="KRIFS, Configure Action Types, Add Action Type"}
{assign var="paging_urls" value="/krifs, /krifs/manage_action_types"}
{include file="paging.html"}

<h1>Add Action Type</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Action type definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="action_type[name]" value="{$action_type->name|escape}" size="50"/></td>
	</tr>
	<tr>
		<td class="highlight">ERP id:</td>
		<td class="post_highlight">
			<input type="text" name="action_type[erp_id]" value="{$action_type->erp_id|escape}" size="20" />
		</td>
	</tr>
	<tr>
		<td class="highlight">Category:</td>
		<td class="post_highlight">
			<select name="action_type[category]">
				<option value="">[Select category]</option>
				{html_options options=$ACTYPES selected=$action_type->category}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Price type:</td>
		<td class="post_highlight">
			<select name="action_type[price_type]">
				<option value="">[Select type]</option>
				{html_options options=$PRICE_TYPES selected=$action_type->price_type}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Contract types:</td>
		<td class="post_highlight">
			<select name="action_type[contract_types]">
				<option value="">[Select type]</option>
				{html_options options=$CONTRACT_TYPES selected=$action_type->contract_types}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Billable:</td>
		<td class="post_highlight">
			<select name="action_type[billable]">
				<option value="0">No</option>
				<option value="1" {if $action_type->billable}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="action_type[comments]" rows="4" cols="50">{$action_type->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />

</form>
