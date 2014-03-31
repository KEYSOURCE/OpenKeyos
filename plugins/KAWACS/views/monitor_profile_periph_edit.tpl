{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Peripherals Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles_periph"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
function item_changed (id)
{
	frm = document.forms['profile'];
	upd_item = document.getElementById('item_update_'+id);
	log_item = document.getElementById('item_log_'+id);
	
	if (frm.elements['profile[items]['+id+']'].checked)
	{
		upd_item.style.visibility = 'visible';
		log_item.style.visibility = 'visible';
	}
	else
	{
		upd_item.style.visibility = 'hidden';
		log_item.style.visibility = 'hidden';
	}
}
{/literal}
//]]>
</script>


<h1>Edit Peripherals Monitor Profile</h1>
<p/>

<form action="" method="post" name="profile">
{$form_redir}

<p class="error">{$error_msg}</p>

<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Profile details</td>
	</tr>
	</thead>
	
	<tr>
		<td width="120" class="highlight">Name:</td>
		<td class="post_highlight"><input type="text" name="profile[name]" size="40" value="{$profile->name}" /></td>
	</tr>
	<tr>
		<td class="highlight">Description:</td>
		<td class="post_highlight">
			<textarea name="profile[description]" rows="4" cols="60">{$profile->description|escape}</textarea>
		</td>
	</tr>
</table>
<p/>
<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />&nbsp;&nbsp;|&nbsp;&nbsp; 
<input type="submit" name="copy" value="Copy to new profile &#0187;" class="button" />
<p/>

<h2>Profile Alerts</h2>
<p/>
    {assign var="p" value="id:"|cat:$profile->id}
[ <a href="{'kawacs'|get_link:'monitor_profile_periph_alerts_edit':$p:'template'}">Edit alerts &#0187;</a> ]
<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Alert name</td>
		<td width="60">Level</td>
		<td>Item</td>
		<td>Ignore days</td>
	</tr>
	</thead>
	
	{foreach from=$profile->alerts item=alert}
	<tr>
		<td>{$alert->id}</td>
		<td>{$alert->name|escape}</td>
		<td>
			{assign var="level" value=$alert->level}
			<font color="{$ALERT_COLORS.$level}"><b>{$ALERT_NAMES.$level}</b></font>
		</td>
		<td>[{$alert->item_id}] {$alert->itemdef->name}</td>
		<td>
			{if $alert->ignore_days}
				{foreach from=$DAY_NAMES key=day_id item=day_name}
					{if ($alert->ignore_days&$day_id)==$day_id}{$day_name},{/if}
				{/foreach}
			{else}-{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="6" class="light_text">[No alerts assigned]</td>
	</tr>
	{/foreach}
</table>
<p/>

<h2>Profile Items</h2>

<p/>

{assign var="cols" value=2}
<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="{$cols}">
            {assign var="p" value="id:"|cat:$profile->id}
            <a href="{'kawacs'|get_link:'monitor_profile_periph_items':$p:'template'}">Select items &#0187;</a></td>
		
		<td nowrap="nowrap">Update (min.)</td>
		<td>Log</td>
		<td colspan="3"> | </td>
		<td nowrap="nowrap">Update (min.)</td>
		<td>Log</td>
	</tr>
	</thead>
	
	{foreach from=$profile->items key=item_id item=item name=items}
		{if ($smarty.foreach.items.index % $cols) == 0}<tr>{/if}
			<td width="25">
			    {assign var="p" value="id:"|cat:$item_id}
			    <a href="{'kawacs'|get_link:'monitor_item_edit':$p:'template'}">{$item_id}</a></td>
			<td width="160"><a href="{'kawacs'|get_link:'monitor_item_edit':$p:'template'}">{$item->itemdef->name}</a></td>
			
			<td width="40"><input type="text" size="4" name="items[{$item_id}][update_interval]" value="{$item->update_interval}" /></td>
			<td width="120">
				<select name="items[{$item_id}][log_type]" style="width:120px;">
					{html_options options=$MONITOR_LOG selected=$item->log_type}
				</select>
			</td>
			
		{if ($smarty.foreach.items.index % $cols) == ($cols-1)}</tr>
		{else}<td width="80"> | </td>
		{/if}
	{foreachelse}
	<tr>
		<td colspan="{$cols}" class="light_text">[No items selected yet for this profile]</td>
	</tr>
	{/foreach}
	{if ($smarty.foreach.categories.iteration % $cols) != 0}</tr>{/if}
</table>
<p/>


<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
