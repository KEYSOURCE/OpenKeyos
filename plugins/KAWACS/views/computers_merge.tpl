{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Merge Computers"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Merge Computers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="40%" class="list">
	<thead>
	<tr>
		<td>Computer:</td>
		<td>{$computer->netbios_name|escape}</td>
	</tr>
	</thead>
	<tr>
		<td>ID:</td>
		<td>{$computer->id}</td>
	</tr>
	<tr>
		<td>MAC address:</td>
		<td>{$computer->mac_address}</td>
	</tr>
	<tr>
		<td>Last contact:</td>
		<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
	</tr>
</table>

<p>Select below the computer with which the above computer should be merged:</p>

<table width="70%" class="list">
	<thead>
	<tr>
		<td width="10"> </td>
		<td width="20">ID</td>
		<td width="50%">Name</td>
		<td width="25%">MAC Address</td>
		<td width="25%">Last contact</td>
	</tr>
	</thead>
	
	
	{if $identical_macs}
		<tr class="cathead">
			<td colspan="5">
			[Computers with same MAC address]
			</td>
		</tr>
		{foreach from=$identical_macs item=computer}
		<tr>
			<td> 
				<input type="radio" class="radio" name="selected_id" value="{$computer->id}">
			</td>
			<td>{$computer->id}</td>
			<td>{$computer->netbios_name|escape}</td>
			<td>{$computer->mac_address}</td>
			<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
		</tr>
		{/foreach}
	{/if}

	
	{if $identical_names}
		<tr class="cathead">
			<td colspan="5">
			[Computers with same Netbios name]
			</td>
		</tr>
		{foreach from=$identical_names item=computer}
		<tr>
			<td> 
				<input type="radio" class="radio" name="selected_id" value="{$computer->id}">
			</td>
			<td>{$computer->id}</td>
			<td>{$computer->netbios_name|escape}</td>
			<td>{$computer->mac_address}</td>
			<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
		</tr>
		{/foreach}
	{/if}
	
	
	<tr class="cathead">
		<td colspan="5">
		[Other computers]
		</td>
	</tr>
	
	{foreach from=$computers item=computer}
	<tr>
		<td>
			<input type="radio" class="radio" name="selected_id" value="{$computer->id}">
		</td>
		<td>{$computer->id}</td>
		<td>{$computer->netbios_name|escape}</td>
		<td>{$computer->mac_address}</td>
		<td>{$computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
	</tr>
	{/foreach}

</table>
<p/>

<input type="submit" name="do_merge" value="Merge with selected">
<input type="submit" name="cancel" value="Cancel">
</form>
