{assign var="paging_titles" value="KAWACS, Manage Profiles - Computers"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Manage Profiles - Computers</h1>

<p class="error">{$error_msg}</p>

Default profile for new computers:
{if $default_profile}<b>{$default_profile->name} (ID: {$default_profile->id})</b>
{else}<font class="error">[None set !!!]</font>
{/if}
| <a href="{'kawacs'|get_link:'monitor_profile_default'}">Edit &#0187;</a>
</b>

<p>

<a href="{'kawacs'|get_link:'monitor_profile_add'}">Add new profile &#0187;</a>
<br>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">Id</td>
		<td width="29%">Name</td>
		<td width="50%">Description</td>
		<td width="5%">Computers</td>
		<td width="5%" align="right">Hearbeat</td>
		<td width="5%" align="right">Alert&nbsp;at</td>
		<td width="5%"> </td>
	</thead>
	<tr>
	
	{foreach from=$profiles item=profile}
		<tr>
			<td>{$profile->id}</td>
			<td>
                {assign var="p" value="id:"|cat:$profile->id}
                <a href="{'kawacs'|get_link:'monitor_profile_edit':$p:'template'}">{$profile->name}</a></td>
			<td>{$profile->description}</td>
			<td nowrap>
				{assign var="profile_id" value=$profile->id}
				{if $computers_count.$profile_id}
					<a href="{'kawacs'|get_link:'monitor_profile_computers':$p:'template'}">{$computers_count.$profile_id}&nbsp;&nbsp;&nbsp;&#0187;</a>
				{else}
					-
				{/if}
			</td>
			<td align="right">{$profile->report_interval} min.</td>
			<td align="right">
				{if $profile->alert_missed_cycles} {$profile->alert_missed_cycles}
				{else} -
				{/if}
			</td>
			
			<td>
				<a href="{'kawacs'|get_link:'monitor_profile_delete':$p:'template'}"
				onClick="return confirm('Are you REALLY sure you want to delete the profile \'{$profile->name}\'?');">Delete</a>
			</td>
		</tr>
	{foreachelse}
	
		<tr><td colspan=4>
		[No monitoring profiles have been defined so far]
		</td></tr>
	
	{/foreach}

</table>
<p>
<a href="{'kawacs'|get_link:'monitor_profile_add'}">Add new profile &#0187;</a>
