
<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
// Called when an hour and duration is selected, pass the selected interval to the calling parent window
function do_select (hour_start, hour_end)
{
	if (window.opener && !window.opener.closed)
	{
		// Send data back to the caller window
		parent_wind = window.opener;
		parent_wind.pass_data_hour (hour_start, hour_end);
	}
	do_close ();
}

// Called when the "Cancel" button is closed, to close the current window without "saving" any data to calling window
function do_close ()
{
	window.close ();
}
{/literal}
//]]>
</script>

<div style="disply:block; padding: 10px;">

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="80">Start hour</td>
		<td colspan="{$hours.12|@count}">Duration</td>
	</tr>
	</thead>
	
	{foreach from=$hours key=hour item=intervals}
	<tr>
		<td {if $hour>=8 and $hour<=17}class="highlight" style="font-weight:bold;"{/if}>{$intervals[0].hour_start}</td>
		{foreach from=$intervals item=interval}
			<td nowrap="nowrap" {if $hour>=8 and $hour<=17}class="highlight"{/if}>
			<a href="#" onclick="do_select('{$interval.hour_start}','{$interval.hour_end}'); return false;">{$interval.duration}</a>&nbsp;&nbsp;&nbsp;
			</td>
		{/foreach}
	</tr>
	{/foreach}
</table>
<p/>
[ <a href="#" onclick="do_close(); return false;"><b>Close</b></a> ]
</div>
