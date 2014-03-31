
<script language="JavaScript" type="text/javascript">
//<![CDATA[

// Set the window size
window.resizeTo (500, 450);

//]]>
</script>


<div style="display: block; margin: 10px;">
<form action="" method="POST" name="frm_t">
{$form_redir}

<h2>Fill Timesheet Gaps</h2>

<p>
Specify below the activity details that you would like to use and
the time intervals you woul like to "fill" with that activity.
</p>
<p class="error">{$error_msg}</p>

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Activity details</td>
	</tr>
	</thead>
	
	<tr>
		<td width="80">Activity:</td>
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
	
	<tr class="head">
		<td colspan="2">Select time intervals</td>
	</tr>
	
	{assign var="intervals_found" value=0}
	{foreach from=$timesheet->hours item=interval key=hours_idx}
	<tr>
		{if !isset($interval->detail_idx)}
			{assign var="intervals_found" value=1}
			<td align="right">
				<input type="checkbox" name="detail[intervals][]" value="{$hours_idx}" class="checkbox"
					{if in_array($hours_idx, $intervals)} checked {/if}
				/>
				<input type="hidden" name="times_in[{$hours_idx}]" value="{$interval->time_in}" />
				<input type="hidden" name="times_out[{$hours_idx}]" value="{$interval->time_out}" />
			</td>
			<td nowrap="nowrap">
				{$interval->time_in|date_format:$smarty.const.HOUR_FORMAT_SMARTY} -
				{$interval->time_out|date_format:$smarty.const.HOUR_FORMAT_SMARTY}
			</td>
		{/if}
	</tr>
	{/foreach}
	
	{if !$intervals_found}
	<tr>
		<td colspan="2" class="light_text">[No available intervals found]</td>
	</tr>
	{/if}
</table>
<p/>

<input type="submit" name="save" value="Fill the gaps" class="button" 
	onclick="return confirm('Are you sure you want to fill the selected intervals with the selected activity?');"
/>
<input type="submit" name="cancel" value="Cancel" class="button" onclick="window.close();" />
</form>
</div>
