{assign var="paging_titles" value="KAWACS, Manage Profiles - Peripherals"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}


<h1>Manage Profiles - Peripherals</h1>

<p class="error">{$error_msg}</p>

<p>
These are monitor profiles which can be assigned to peripherals of various
types and/or brands.<br/>
Note that if you want to monitor a peripheral it is not enough to assign 
a profile to it, you also need to specify a computer which will collect
data about it using SNMP.
</p>

<p><a href="{'kawacs'|get_link:'monitor_profile_periph_add'}">Add new profile &#0187;</a></p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="29%">Name</td>
		<td width="50%">Description</td>
		<td width="5%">Peripherals</td>
		<td width="10%" align="right">Hearbeat</td>
		<td width="5%"> </td>
	</thead>
	<tr>
	
	{foreach from=$profiles item=profile}
		<tr>
			<td>{$profile->id}</td>
			<td>
                {assign var="p" value="id:"|cat:$profile->id}
                <a href="{'kawacs'|get_link:'monitor_profile_periph_edit':$p:'template'}">{$profile->name}</a></td>
			<td>{$profile->description}</td>
			<td nowrap>
				{assign var="profile_id" value=$profile->id}
				{if $peripherals_count.$profile_id}
					<a href="{'kawacs'|get_link:'monitor_profile_peripherals':$p:'template'}">{$peripherals_count.$profile_id}&nbsp;&nbsp;&nbsp;&#0187;</a>
				{else}
					-
				{/if}
			</td>
			<td align="right">{$profile->report_interval} min.</td>
			<td>
				<a href="{'kawacs'|get_link:'monitor_profile_periph_delete':$p:'template'}"
				onClick="return confirm('Are you REALLY sure you want to delete the profile \'{$profile->name}\'?');">Delete</a>
			</td>
		</tr>
	{foreachelse}
	
		<tr>
			<td colspan="6" class="light_text">[No monitoring profiles have been defined so far]</td>
		</tr>
	
	{/foreach}

</table>
<p>
<a href="{'kawacs'|get_link:'monitor_profile_periph_add'}">Add new profile &#0187;</a>
