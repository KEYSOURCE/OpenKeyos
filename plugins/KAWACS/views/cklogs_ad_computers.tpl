{assign var="paging_titles" value="KAWACS, Manage AD Computers Space Logs"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Manage AD Computers Space Logs</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

Months:
<select name="month">
	<option value="">[Select month]</option>
	<option value="current">[last_week]</option>
	{html_options output=$months values=$months selected=$month}
</select>

&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="do_check" value="Check stat" class="button" />
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="do_update" value="Update stat" class="button" 
	onclick="return confirm('Are you really sure you want to proceed with updating? It can take a long time.');"
/>
</form>
<p/>

<table width="60%" class="list">
{if $checked_stat}
		<thead>
		<tr>
			<td colspan="2">Status for: {$stats.month}</td>
		</tr>
		</thead>
		
		<tr>
			<td width="20%" class="highlight">Total sets:</td>
			<td class="post_highlight">{$stats.total}</td>
		</tr>
		<tr>
			<td class="highlight">Can delete (1046):</td>
			<td class="post_highlight">{$stats.total_delete_1046}</td>
		</tr>
		<tr>
			<td class="highlight">Can keep (1046):</td>
			<td class="post_highlight">{$stats.total_keep_1046}</td>
		</tr>
		<tr>
			<td class="highlight">Can delete (1047):</td>
			<td class="post_highlight">{$stats.total_delete_1047}</td>
		</tr>
		<tr>
			<td class="highlight">Can keep (1047):</td>
			<td class="post_highlight">{$stats.total_keep_1047}</td>
		</tr>
		<tr>
			<td class="highlight">Duration:</td>
			<td class="post_highlight">{$stats.time} sec.</td>
		</tr>
{elseif $updated_stat}
		<thead>
		<tr>
			<td colspan="2">Update log for: {$stats.month}</td>
		</tr>
		</thead>
		
		<tr>
			<td width="20%" class="highlight">Total sets:</td>
			<td class="post_highlight">{$stats.total}</td>
		</tr>
		<tr>
			<td class="highlight">Deleted (1046):</td>
			<td class="post_highlight">{$stats.total_delete_1046}</td>
		</tr>
		<tr>
			<td class="highlight">Kept (1046):</td>
			<td class="post_highlight">{$stats.total_keep_1046}</td>
		</tr>
		<tr>
			<td class="highlight">Deleted (1047):</td>
			<td class="post_highlight">{$stats.total_delete_1047}</td>
		</tr>
		<tr>
			<td class="highlight">Kept (1047):</td>
			<td class="post_highlight">{$stats.total_keep_1047}</td>
		</tr>
		<tr>
			<td class="highlight">Duration (processing):</td>
			<td class="post_highlight">{$stats.process_time} sec.</td>
		</tr>
		<tr>
			<td class="highlight">Duration (other data):</td>
			<td class="post_highlight">{$stats.data_time} sec.</td>
		</tr>
		<tr>
			<td class="highlight">Total duration:</td>
			<td class="post_highlight">{$stats.time} sec.</td>
		</tr>
{/if}

</table>
<p/>
