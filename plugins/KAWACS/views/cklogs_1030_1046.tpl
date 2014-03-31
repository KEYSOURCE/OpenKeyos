{assign var="paging_titles" value="KAWACS, Update Logs 1030-1046"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Update Logs 1030-1046</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

Months:
<select name="month">
	<option value="">[Select month]</option>
	<option value="current">[last_week]</option>
	<option value="real">[Real]</option>
	{html_options output=$months values=$months selected=$month}
</select>

&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="do_update" value="Update stat" class="button" 
	onclick="return confirm('Are you really sure you want to proceed with updating? It can take a long time.');"
/>
</form>
<p/>

<table width="60%" class="list">
{if $updated_stat}
		<thead>
		<tr>
			<td colspan="2">Updated log for: {$stats.month}</td>
		</tr>
		</thead>
		<tr>
			<td width="20%" class="highlight">Initial sets:</td>
			<td class="post_highlight">{$stats.initial}</td>
		</tr>
		<tr>
			<td class="highlight">Final sets 1046:</td>
			<td class="post_highlight">{$stats.final_1046}</td>
		</tr>
		<tr>
			<td class="highlight">Final sets 1047:</td>
			<td class="post_highlight">{$stats.final_1047}</td>
		</tr>
		<tr>
			<td class="highlight">Duration:</td>
			<td class="post_highlight">{$stats.time} sec.</td>
		</tr>
{/if}

</table>
<p/>
