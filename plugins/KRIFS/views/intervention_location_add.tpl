{assign var="paging_titles" value="KRIFS, Configure Intervention Locations, Add Location"}
{assign var="paging_urls" value="/krifs, /krifs/manage_intervention_locations"}
{include file="paging.html"}

<h1>Add Intervention Location</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td colspan="2">Intervention location definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="location[name]" value="{$location->name|escape}" size="30"/></td>
	</tr>
	<tr>
		<td class="highlight">On site:</td>
		<td class="post_highlight">
			<select name="location[on_site]">
				<option value="0">No</option>
				<option value="1" {if $location->on_site}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Helpdesk:</td>
		<td class="post_highlight">
			<select name="location[helpdesk]">
				<option value="0">No</option>
				<option value="1" {if $location->helpdesk}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add" />
<input type="submit" name="cancel" value="Cancel" />

</form>
