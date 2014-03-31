{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Computer Events Settings"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:"kawacs"|get_link:"computer_view":$p:"template"}
{include file="paging.html"}

<h1>Computer Events Log Reporting: #{$computer->id}: {$computer->netbios_name|escape}</h1>
<p class="error">{$error_msg}</p>

<p>Below you can define additional events to request for this computer, other than the ones defined by the profile.</p>

<p>
 {assign var="p" value="id:"|cat:$computer->id}
[ <a href="{'kawacs':get_link:'computer_view':$p:'template'}">&#0171 Back to computer</a> ]

{if $computer_has_default_settings}
    {assign var="p" value="id:"|cat:$computer->id}
[ <a href="{'kawacs':get_link:'computer_events_revert_to_profile':$p:'template'}"
onclick="return confirm('Are you sure you want to delete all the computer settings?\nIf you do this, the computer will use the settings from the associated profile.');"
>Revert to profile's settings</a> ]
{/if}

</p>

{if !$computer_has_default_settings}
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="140">Default Events Types</td>
			<td class="post_highlight" nowrap="nowrap">
                {assign var="p" value="id:"|cat:$computer->id}
				<a href="{'kawacs'|get_link:'computer_events_settings_edit':$p:'template'}"
				onclick="return confirm('Are you really sure you want to define specific settings for this computer?');"
				>Edit &#0187;</a>
			</td>
		</tr>
		</thead>
		<tr>
			<td colspan="2" class="light_text">[Use profile's settings]</td>
		</tr>
	</table>
{else}
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="140">Category</td>
			<td class="post_highlight">
                {assign var="p" value="id:"|cat:$computer->id}
				Default events types&nbsp;|&nbsp;<a href="{'kawacs'|get_link:'computer_events_settings_edit':$p:'template'}"
				>Edit&nbsp;&#0187;</a>
			</td>
		</tr>
		</thead>
		
		{foreach from=$computer->default_events_types_requested key=cat_id item=types}
		<tr>
			<td class="highlight">{$EVENTS_CATS.$cat_id|escape}</td>
			<td class="post_highlight">{$computer->get_events_types_str($cat_id)|escape}</td>
		</tr>
		{/foreach}
		
	</table>
	<h3>Additional Sources</h3>
    {assign var="p" value="id:"|cat:$computer->id}
	[ <a href="{'kawacs'|get_link:'computer_events_src_add':$p:'template'}">Add Events Source &#0187;</a> ]
	{if count($computer->events_types_requested)>0}
		<table class="list" width="98%">
			<thead>
			<tr>
				<td width="120">Category</td>
				<td>Source</td>
				<td>Events types</td>
				<td width="140"> </td>
			</tr>
			</thead>
			
			{foreach from=$computer->events_types_requested item=src}
			<tr>
				<td>
					{assign var="cat_id" value=$src->category_id}
					{$EVENTS_CATS.$cat_id|escape}
				</td>
				<td>
					{assign var="source_id" value=$src->source_id}
					{$sources.$cat_id.$source_id|escape}
				</td>
				<td nowrap="nowrap">{$src->get_events_types_str()|escape}</td>
				<td nowrap="nowrap" align="right">
                    {assign var="p" value="id:"|cat:$computer->id|cat:',src_id:'|cat:$src->id}
					<a href="{'kawacs'|get_link:'computer_events_src_edit':$p:'template'}">Edit</a> |
					<a href="{'kawacs'|get_link:'computer_events_src_delete':$p:'template'}"
					onclick="return confirm('Are you sure you want to delete this?');"
					>Delete</a>
				</td>
			</tr>
			{/foreach}
		</table>
	{/if}
{/if}
<p/>


<h2>Profile Settings for Events Log Reporting</h2>

<p>The settings below are defined in the computer's profile.</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="140">Category</td>
		<td width="160" class="post_highlight">Default events types</td>
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
