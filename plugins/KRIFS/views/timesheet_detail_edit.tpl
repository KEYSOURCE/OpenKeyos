{assign var="timesheet_id" value=$timesheet->id}
{assign var="paging_titles" value="KRIFS, Timesheets, Edit Timesheet, Edit Timesheet Detail"}
{assign var="paging_urls" value="/krifs, /manage_timesheets, /krifs/timesheet_edit/"|cat:$timesheet_id}
{include file="paging.html"}

<h1>Edit Timesheet Time: {$timesheet->date|date_format:$smarty.const.DATE_FORMAT_SMARTY}, {$timesheet->user->get_name()}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}


<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2">Define activity details</td>
	</tr>
	</thead>

	<tr>
		<td width="10%">Time in:</td>
		<td>
			<input type="text" name="detail[time_in]" size="6" value="{$detail->time_in|date_format:$smarty.const.HOUR_FORMAT_SMARTY}" />
		</td>
	</tr>
	<tr>
		<td>Time out:</td>
		<td>
			<input type="text" name="detail[time_out]" size="6" value="{$detail->time_out|date_format:$smarty.const.HOUR_FORMAT_SMARTY}" />
		</td>
	</tr>
	<tr>
		<td>Activity:</td>
		<td>
			<select name="detail[activity_id]">
				<option value="">[Select activity]</option>
				{html_options options=$activities selected=$detail->activity_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Location:</td>
		<td>
			<select name="detail[location_id]">
				<option value="">[Select location]</option>
				{html_options options=$locations_list selected=$detail->location_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Customer:</td>
		<td>
			<select name="detail[customer_id]">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$detail->customer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="detail[comments]" rows="4" cols="60">{$detail->comments|escape}</textarea>
		</td>
	</tr>
	
</table>
<p/>

<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>
