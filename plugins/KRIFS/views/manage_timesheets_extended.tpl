{assign var="paging_titles" value="Krifs, Manage Timesheets Extended"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<script language="javascript" type="text/javascript" src="/javascript/CalendarPopup.js"></script>
<script language="javascript" type="text/javascript">
	//<[!CDATA[
	var show_states = new Array();
	{assign var="i" value="0"}
	{foreach from=$users_list key="uid" item="name"}
		show_states[{$i}] = new Array('{$uid}',1);
		{assign var="i" value=$i+1}
	{/foreach}
	//alert(show_states);
	{literal}
	function showDetailed(ck_uid, uid)
	{
		var _hck = document.getElementById(ck_uid);
		
		for(var i=0; i < show_states.length; i++)
		{
			var ss = show_states[i];
			if(uid == ss[0]) ss[1] = _hck.checked ? 0 : 1;  
		}
		set_display_states();
	}
	function showDetailedAll()
	{
		var _hck = document.getElementById("_showDetailsAll");
		var _lbl = document.getElementById("_showLabelAll");
		for(var i=0; i < show_states.length; i++)
		{
			var ss = show_states[i];
			ss[1] = _hck.checked ? 0 : 1;  
		}
		_hck.checked ? _lbl.innerHTML="Show all" : _lbl.innerHTML = "Hide all";
		
		set_display_states();
	}
	function set_display_states()
	{
		for(var i=0; i < show_states.length;i++)
		{
			//alert(i);
			var ss = show_states[i];
			var d_name = 'ut_body_'+ss[0];
			//alert(d_name);
			var ut_div = document.getElementById(d_name);
			var ck_name = "_showDetailed["+ss[0]+"]";
			var lbl_name = "_showLabel["+ss[0]+"]";
			var ck_control = document.getElementById(ck_name);
			var lbl_control = document.getElementById(lbl_name);
			if(eval(ss[1]) == 1)
			{
				ck_control.checked = false;
				lbl_control.innerHTML = "Hide";
				ut_div.style.display = 'block';
			}
			else
			{	
				ck_control.checked = "checked";
				lbl_control.innerHTML = "Show";			
				ut_div.style.display = 'none';
			}
		}
	}
	{/literal}
	//]]>
</script>
<h1>Manage Timesheets Extended</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter">
{$form_redir}
<table class="list" width="98%">
	<tr class="head">
		<td>
			<input type="text" size="10" name="date_from" value="{if $filter.date_from}{$filter.date_from|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}" >
			{literal}
				<a href="#" onClick="showCalendarSelector('filter', 'date_from'); return false;" name="anchor_calendar" id="anchor_calendar">
					<img src=/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle;">
				</a>
			{/literal}
			-
			<input type="text" size="10" name="date_to" value="{if $filter.date_to}{$filter.date_to|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}">
			{literal}
				<a href="#" onclick="showCalendarSelector('filter', 'date_to'); return false;" name="anchor_calendar" id="anchor_calendar">
					<img src=/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle;">
				</a>
			{/literal}			
		</td>
		<td width="20px;">
			<input type="checkbox" id="_showDetailsAll" name="_showDetailsAll" onchange="showDetailedAll();">
		</td>
		<td>
			<pre id="_showLabelAll" name="_showLabelAll" style="align:left;">Hide all</pre>
		</td>
		<td>
			<input type="submit" value="Generate pdf report">
		</td>
	</tr>
</table>
<p />
{foreach from=$timesheets item="_tsheet" key="uid"}
<div name="ut_head_{$uid}">
	<table class="list" width="95%">	
	<thead>
		<tr>
			<td width="70%">{$users_list.$uid}</td>
			<td width="20px" nowrap="nowrap"><input type="checkbox" id="_showDetailed[{$uid}]" name="_showDetailed[{$uid}]" onchange="showDetailed('_showDetailed[{$uid}]', {$uid});"></td>
			<td><pre id='_showLabel[{$uid}]' name='_showLabel[{$uid}]' style="align:left;"></pre></td>
		</tr>
	</thead>
	</table>
</div>
<div id="ut_body_{$uid}" name="ut_body_{$uid}">
<table class="list" width="95%">	
	<thead>
		<tr>
			<td colspan="2" width="18%">Date</td>
			<td width="9%">ID</td>
			<td width="18%">Status</td>
			<td width="25%">Defined time</td>
			<td width="30%">Total time</td>
		</tr>
	</thead>
	{foreach from=$_tsheet key=day item=timesheet}
	<tr>
		<td width="1%">
			<a 
				{if $timesheet->id}
                    {assign var="p" value="id:"|cat:$timesheet->id}
					href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
				{else}
                    {assign var="p" value="date:"|cat:$day|cat:',user_id'|cat:$uid}
					href="{'krifs'|get_link:'timesheet_add':$p:'template'}"
				{/if}
			>{$day|date_format:"%a"}</a>
		</td>
		<td >
			<a 
				{if $timesheet->id}
                    {assign var="p" value="id:"|cat:$timesheet->id}
					href="{'krifs'|get_link:'timesheet_edit':$p:'template'}"
				{else}
                    {assign var="p" value="date:"|cat:$day|cat:',user_id'|cat:$uid}
					href="{'krifs'|get_link:'timesheet_add':$p:'template'}"
				{/if}
			>{$day|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			
			</a>
		</td>
		<td>
			{if $timesheet->id}
				[{$timesheet->id}]
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="status" value=$timesheet->status}
			{$TIMESHEET_STATS.$status}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="defined_work_time" value=$timesheet->get_defined_work_time()}
			{if $defined_work_time}
				{$defined_work_time|@format_interval_minutes}
			{else}
				--
			{/if}
		</td>
		<td {if !$timesheet->id}class="light_text"{/if}>
			{assign var="work_time" value=$timesheet->get_work_time()}
			{if $work_time}
				{$work_time|@format_interval_minutes}
			{else}
				--
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="6">
		<table class="list" width="100%">
			<thead>
				<tr>
					<td colspan="2" width="5%">Hour</td>
					<td width="20%">Activity/Action type</td>
					<td width="10%">Location</td>
					<td width="20%">Customer</td>
					<td width="44%" colspan="2">Ticket detail / Comments</td>
				</tr>
			</thead>
			{foreach from=$timesheet->hours item=interval}
			<tr>
				<td width="1%" nowrap="nowrap"
					{if !isset($interval->detail_idx)}class="light_text"
					{elseif $interval->overlaps}class="error"{/if}
				>
					{$interval->time_in|date_format:$smarty.const.HOUR_FORMAT_SMARTY}
				</td>
				<td width="4%" nowrap="nowrap"
					{if !isset($interval->detail_idx)}class="light_text"
					{elseif $interval->overlaps}class="error"{/if}
				>
					- {$interval->time_out|date_format:$smarty.const.HOUR_FORMAT_SMARTY}
				</td>
				
				{if isset($interval->detail_idx)}
					{assign var="idx" value=$interval->detail_idx}
					{assign var="detail" value=$timesheet->details.$idx}
					<td>
						{if $detail->ticket_detail_id}
							{assign var="action_type_id" value=$detail->ticket_detail->activity_id}
							{$action_types_list.$action_type_id}
						{else}
							{assign var="activity_id" value=$detail->activity_id}
							{$activities.$activity_id}
						{/if}
					</td>
					<td>
						{assign var="location_id" value=$detail->location_id}
						{$locations_list.$location_id}
					</td>
					<td>
						{assign var="customer_id" value=$detail->customer_id}
						{$customers_list.$customer_id} (# {$customer_id})
					</td>
					<td>
						{if $detail->ticket_detail_id}
							<i>
                            {assign var="p" value="id:"|cat:$detail->ticket_detail_id|cat:",returl:"|cat:$ret_url}
							<a href="{'krifs'|get_link:'ticket_detail_edit':$p:'template'}"
							># {$detail->ticket_detail->id}</a>:
							{$detail->ticket->subject|escape}
							</i>
							<br/>
							{$detail->ticket_detail->comments|escape|nl2br}
						{else}
							{$detail->comments|escape|nl2br}
						{/if}
					</td>
				{else}
					<td colspan="4"> </td>
				{/if}
				
			</tr>
			{/foreach}
		</table>
		</td>
	</tr>
	{/foreach}
</table>
</div>
<p />
{/foreach}
</form>
<script language="javascript" type="text/javascript">
	//<[!CDATA[
	{literal}
	//alert(show_states.length)
	set_display_states()
	{/literal}
	//]]>
</script>