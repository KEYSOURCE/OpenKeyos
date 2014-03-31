{assign var="profile_id" value=$profile->id}
{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Monitor Profile, Select Monitor Items"}
{assign var="p" value="id:"|cat:$profile_id}
{assign var="monitor_profile_events_edit_link" value="kawacs"|get_link:"monitor_profile_events_edit":$p:"template"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_profiles_periph, "|cat:$monitor_profile_events_edit}
{include file="paging.html"}

<h1>Profile Items: {$profile->name|escape}</h1>
<p/>

<p>Please select below the monitor items to be included in this profile.</p>

<form action="" method="post" name="frm_t">
{$form_redir}

{assign var="cols" value=3}
{assign var="col_width" value="33%"}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="{$cols}">Available Monitor Items</td>
		<td colspan="{$cols}"></td>
	</tr>
	</thead>
	
	{foreach from=$items item=item name=items}
		{if ($smarty.foreach.items.index % $cols) == 0}<tr>{/if}
		
		<td width="{$col_width}">
			{assign var="item_id" value=$item->id}
			<input type="checkbox" value="{$item->id}" name="profile_items[]" class="checkbox"
			{if isset($profile->items.$item_id)}checked{/if}
			/>&nbsp;{$item->id}:
			{$item->name}
		</td>
		
		{if ($smarty.foreach.items.index % $cols) == $cols-1}</tr>{/if}
	
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
