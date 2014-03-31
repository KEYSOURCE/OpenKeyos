{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Monitor Profile "}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles"}
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


<h1>Edit Monitor Profile</h1>
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
		<td class="post_highlight"><input type="text" name="profile[name]" size="20" value="{$profile->name}" /></td>
	</tr>
	<tr>
		<td class="highlight">Heartbeat:</td>
		<td class="post_highlight">
			<input type="text" name="profile[report_interval]" size="6" value="{$profile->report_interval}" />
			(in minutes, fractions allowed)
		</td>
	</tr>
	<tr>
		<td class="highlight">Alert at:</td>
		<td class="post_highlight">
		<input type="text" name="profile[alert_missed_cycles]" size="6" value="{if $profile->alert_missed_cycles>0}{$profile->alert_missed_cycles}{/if}"/>
			missed cycles
			(leave empty if no alerting is needed)
		</td>
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
[ <a href="/kawacs/monitor_profile_alerts_edit/{$profile->id}">Edit alerts &#0187;</a> ]
<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Alert name</td>
		<td width="60">Level</td>
		<td nowrap align="center" width="70">On contact only</td>
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
		<td align="center">
			{if $alert->on_contact_only}Y{/if}
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

{if isset($profile->items[$smarty.const.EVENTS_ITEM_ID])}
<h2>Events Log Reporting</h2>

<p/>
[ <a href="/kawacs/monitor_profile_events_edit/{$profile->id}">Edit events log reporting &#0187;</a> ]
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="140">Category</td>
		<td width="140" class="post_highlight">Default events types</td>
		<td class="post_highlight">Additional sources</td>
	</tr>
	</thead>
		
	{foreach from=$profile->default_events_types_requested key=cat_id item=types}
	<tr>
		<td class="highlight" nowrap="nowrap">{$EVENTS_CATS.$cat_id}</td>
		<td class="post_highlight" nowrap="nowrap">
			{$profile->get_events_types_str($cat_id)|escape}
		</td>
		<td class="post_highlight">
			{assign var="has_extra" value=0}
			{foreach from=$profile->events_types_requested item=src}
			{if $cat_id == $src->category_id}
				{assign var="has_extra" value=1}
				{assign var="source_id" value=$src->source_id}
				{$sources.$cat_id.$source_id|escape}:
				{$src->get_events_types_str()|escape}<br/>
			{/if}
			{/foreach}
			{if !$has_extra}<font class="light_text">--</font>{/if}
		</td>
	</tr>
	{/foreach}
</table>
<p/>
{/if}


<h2>Profile Items</h2>
<p/>

{assign var="cols" value=2}
<table width="100%">

	{foreach from=$available_categories item=items key=category_id name=categories}
		{if ($smarty.foreach.categories.iteration % $cols) == 1}<tr>{/if}
		<td>
		
		<table class="list" width="95%">
		<thead>
		<tr>
			<td colspan="2">Category: {$MONITOR_CAT.$category_id}</td>
			<td colspan="2" style="width:60px; white-space:nowrap; font-weight:400;">Update (min.)</td>
			<td style="width:80px; font-weight:400;">Log</td>
		</tr>
		</thead>
		
		{foreach from=$items item=item}
			<tr>
				{assign var="item_id" value=$item->id}
				{if $profile->items[$item_id]}
					{assign var="update_val" value=$profile->items[$item_id]->update_interval}
					{assign var="log_val" value=$profile->items[$item_id]->log_type}
				{else}
					{assign var="update_val" value=$item->default_update}
					{assign var="log_val" value=$item->default_log}
				{/if}
				<td width="20">{$item->id}</td>
				<td>{$item->name}</td>
				<td width="10">
					<input type="checkbox" name="profile[items][{$item_id}]" value="1" {if isset($profile->items.$item_id)}checked{/if}
						onChange="item_changed({$item_id})"/>
				</td>
				<td>
					<input type="text" name="items[{$item_id}][update_interval]" value="{$update_val}" size="4" id="item_update_{$item_id}" />
				</td>
				
				<td nowrap="nowrap">
					{if $item_id==$smarty.const.EVENTS_ITEM_ID}
					<font class="light_text">[Logging handled separately]</font>
					<input type="hidden" name="items[{$item_id}][log_type]" id="item_log_{$item_id}" value="0" />
					{else}
					<select name="items[{$item_id}][log_type]" id="item_log_{$item_id}">
					{html_options options=$MONITOR_LOG selected=$log_val}
					</select>
					{/if}
					<script language="JavaScript">item_changed({$item_id})</script>
				</td>
			</tr>
		{/foreach}
		</table>
		<br/><br/>
		</td>
		{if ($smarty.foreach.categories.iteration % $cols) == 0}</tr>{/if}
	{/foreach}
	{if ($smarty.foreach.categories.iteration % $cols) != 0}</tr>{/if}
</table>
<p/>


<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
