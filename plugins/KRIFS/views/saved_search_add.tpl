{assign var="paging_titles" value="KRIFS, Save Search"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

{literal}
<script language="JavaScript">

var cancel_clicked = false;

function checkSave ()
{
	frm = document.forms['save_frm']
	searches_list = frm.elements['save_as_search']
	ret = true
	
	if (!cancel_clicked)
	{
		do_replace = frm.elements['save_new'][1].checked
		if (do_replace)
		{
			replacing = searches_list.options[searches_list.selectedIndex].value
			replacing_text = searches_list.options[searches_list.selectedIndex].text
			if (replacing == '')
			{
				alert ('Please select the saved search to replace')
				ret = false
			}
			else
			{
				ret = confirm ('Are you sure you want to replace the saved search "'+replacing_text+'"?');
			}
		}
	}
	
	return ret
}

</script>
{/literal}


<h1>Save Search</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" onSubmit="return checkSave();" name="save_frm">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="3">Saved search details</td>
	</tr>
	</thead>
	
	<tr>
		<td rowspan="3">
			<input type="radio" class="radio" name="save_new" value="1" {if !$search_id}checked{/if}> <b>Save as new search</b>
		</td>
		<td>Name:</td>
		<td>
			<input type="text" name="search[name]" value="{$search->name}" size="60">
		</td>
	</tr>
	<tr>
		<td>Private:</td>
		<td>
			<select name="search[private]">
				<option value="0">No</option>
				<option value="1" {if $search->private}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Add to Favorites:</td>
		<td>
			<select name="search[add_to_favorites]">
				<option value="0">No</option>
				<option value="1" {if $search->add_to_favorites}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td>
			<input type="radio" class="radio" name="save_new" value="0" {if $search_id}checked{/if}> <b>Save as existing search</b>
		</td>
		<td colspan="2">
			<select name="save_as_search">
				{html_options options=$favorites_searches selected=$search_id}
				<option value="">-----------------</option>
				{html_options options=$other_searches selected=$search_id}
			</select>
		</td>
	</tr>
	
	<tr class="head">
		<td colspan="3">Search criteria</td>
	</tr>
	
	<tr>
		<td>Keywords:</td>
		<td colspan="2">
			{if $filter.keywords}
				<b>
				{if $filter.keywords_phrase}By phrase,
				{else}
					By word ({if $filter.keywords_and}AND{else}OR{/if})
				{/if}
				<br>
				In:
				{if $filter.in_subject}subject, {/if}
				{if $filter.in_comments}comments{/if}
				</b><br>
				{$filter.keywords}
			{else}
				[None]
			{/if}
		</td>
	</tr>
	<tr>
		<td>User:</td>
		<td colspan="2">
			{if $filter.user_id}
				<b>
				{if $filter.view==2}Assigned to
				{elseif $filter.view==3}Owned by
				{elseif $filter.view==4}Created by
				{else}[Any involvment]
				{/if}
				:</b><br>
				
				{foreach from=$filter.user_id item=user_id name="list"}
					{$users_list.$user_id}{if !$smarty.foreach.list.last},{/if}
				{/foreach}
			{else}
				[All]
			{/if}
		</td>
	</tr>
	<tr>
		<td>Customer:</td>
		<td colspan="2">
			{if $filter.customer_id}
				{foreach from=$filter.customer_id item=customer_id name="list"}
					{$customers_list.$customer_id}{if !$smarty.foreach.list.last},{/if}
				{/foreach}
			{else}
				[All]
			{/if}
		</td colspan="2">
	</tr>
	<tr>
		<td>Status:</td>
		<td colspan="2">
			{if $filter.status}
				{foreach from=$filter.status item=status_id name="list"}
					{$TICKET_STATUSES.$status_id}{if !$smarty.foreach.list.last},{/if}
				{/foreach}
			{else}
				[All]
			{/if}
			{if $filter.escalated_only}<br/><b>Escalated only</b>{/if}
			{if $filter.not_linked_ir}<br/><b>Not linked to IR</b>{/if}
			{if $filter.not_seen_manager}<br/><b>Not seen by manager</b>{/if}
			{if $filter.not_seen_manager_or_not_ir}<br/><b>Not seen by manager OR not linked to IR</b>{/if}
		</td>
	</tr>
	<tr>
		<td>Type:</td>
		<td colspan="2">
			{if $filter.type}
				{foreach from=$filter.type item=type_id name="list"}
					{$TICKET_TYPES.$type_id}{if !$smarty.foreach.list.last},{/if}
				{/foreach}
			{else}
				[All]
			{/if}
		</td>
	</tr>
	<tr>
		<td>Results per page:</td>
		<td colspan="2">
			{assign var="per_page" value=$filter.limit}
			{$PER_PAGE_OPTIONS.$per_page}
		</td>
	</tr>
	<tr>
		<td>Columns:</td>
		<td colspan="2">
			Owner: {if $filter.show_owner}Yes{else}No{/if}<br>
			Created: {if $filter.show_created}Yes{else}No{/if}<br>
			Escalated: {if $filter.show_escalated}Yes{else}No{/if}
		</td>
	</tr>
</table>
<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel" onclick="cancel_clicked=true;">

</form>
