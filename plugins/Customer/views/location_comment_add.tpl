{assign var="location_id" value=$location->id}
{assign var="paging_titles" value="Customers, Manage Customers Locations, Edit Location, Add Comment"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations, /?cl=customer&op=location_edit&id=$location_id"}
{include file="paging.html"}

<h1>Add Comment</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="70%">
	<thead>
	<tr>
		<td>Location:</td>
		<td class="post_highlight">
			{if $location->parent_id}
				{foreach from=$location->parents item=parent name=parents_loop}
					{$parent->name} &#0187;
				{/foreach}
			{/if}
			{$location->name}
		</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Comments:</td>
		<td class="post_highlight">
			<textarea name="comment[comments]" rows="6" cols="60">{$comment->comments}</textarea>
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>