{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Confirm Computers Merge"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>Confirm Computers Merge</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<p>Please confirm that you want to merge these two computers.</p>

<p>
Since <b>{$computer->netbios_name|escape} (#{$computer->id})</b> has reported information
most recently, this will be the computer being kept.<br/>
The data from the other computer, 
<b>{$selected_computer->netbios_name|escape} (#{$selected_computer->id})</b>, will be
copied and then the computer will then be deleted.
</p>


<table width="60%" class="list">
	<thead>
	<tr>
		<td width="40%">Computer being kept:</td>
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
<p/>

<table width="60%" class="list">
	<thead>
	<tr>
		<td width="40%">Computer being merged and removed:</td>
		<td>{$selected_computer->netbios_name|escape}</td>
	</tr>
	</thead>
	<tr>
		<td>ID:</td>
		<td>{$selected_computer->id}</td>
	</tr>
	<tr>
		<td>MAC address:</td>
		<td>{$selected_computer->mac_address}</td>
	</tr>
	<tr>
		<td>Last contact:</td>
		<td>{$selected_computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
	</tr>
</table>
<p/>

<p>
Note that the following information will be copied from one computer to the other:
<ul>
	<li>All logs of reported items.</li>
	<li>All peripheral assignments.</li>
	<li>All associated KLARA remote services and passwords.</li>
	<li>All associated KLARA phone numbers.</li>
	<li>All logged notifications.</li>
</ul>
</p>

<input type="submit" name="do_merge" value="Merge"
	onClick="return confirm('Are you really sure you want to merge these computers?');"
>
<input type="submit" name="cancel" value="Cancel">
</form>
