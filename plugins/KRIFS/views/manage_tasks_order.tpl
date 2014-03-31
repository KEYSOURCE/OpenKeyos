{assign var="paging_titles" value="Krifs, Scheduled Tasks, Set Tasks Order"}
{assign var="paging_urls" value="/krifs, /krfs/manage_tasks"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<[CDATA[

var tasks_tickets_ids = new Array ();
var tasks_customers = new Array ();
var tasks_hours = new Array ();
var tasks_durations = new Array ();
var tasks_locations = new Array ();
{foreach from=$tasks item=task}
	{assign var="customer_id" value=$task->customer_id}
	{assign var="location_id" value=$task->location_id}
	tasks_tickets_ids[{$task->id}] = '{$task->ticket_id}';
	tasks_customers[{$task->id}] = '{$customers_list.$customer_id} ({$customer_id})';
	tasks_hours[{$task->id}] = '{if $task->hour}{$task->hour}{else}--{/if}';
	tasks_durations[{$task->id}] = '{if $task->duration}{$task->duration}{else}--{/if}';
	tasks_locations[{$task->id}] = '{$locations_list.$location_id}';
{/foreach}

var save_clicked = false;
var bottom_pos = {$tasks|@count} - 1;

{literal}
function doMoveTo (new_pos)
{
	frm = document.forms['data_frm'];
	lst = frm.elements['tasks_list[]'];
	c_idx = lst.selectedIndex;
	
	if (c_idx>=0 && c_idx!=new_pos)
	{
		bk_text = lst.options[c_idx].text;
		bk_value = lst.options[c_idx].value;
		lst.options[c_idx].text = lst.options[new_pos].text;
		lst.options[c_idx].value = lst.options[new_pos].value;
		lst.options[new_pos].text = bk_text;
		lst.options[new_pos].value = bk_value;
		lst.options[c_idx].selected = false;
		lst.options[new_pos].selected = true;
	}
	
	return false;
}

function doMove (dir)
{
	frm = document.forms['data_frm'];
	lst = frm.elements['tasks_list[]'];
	
	if (lst.selectedIndex >= 0)
	{
		new_pos = lst.selectedIndex + dir;
		if (new_pos>=0 && new_pos<lst.length)
		{
			doMoveTo (new_pos);
		}
	}
	else
	{
		alert ('You need to select a task/ticket first');
	}
	
	return false;
}

function showSelectedTask ()
{
	frm = document.forms['data_frm'];
	lst = frm.elements['tasks_list[]'];
	c_idx = lst.selectedIndex;
	
	if (c_idx>=0)
	{
		c_task_id = lst.options[c_idx].value;
		frm.elements['task_ticket_id'].value = tasks_tickets_ids[c_task_id];
		frm.elements['task_hour'].value = tasks_hours[c_task_id];
		frm.elements['task_duration'].value = tasks_durations[c_task_id];
		frm.elements['task_location'].value = tasks_locations[c_task_id];
		frm.elements['task_customer'].value = tasks_customers[c_task_id];
	}
	
}

function doSubmit ()
{
	frm = document.forms['data_frm'];
	lst = frm.elements['tasks_list[]'];

	if (save_clicked)
	{
		lst.multiple = true;
		for (i=0; i<lst.options.length; i++) lst.options[i].selected = true;
	}
	else
	{
		for (i=0; i<lst.options.length; i++) lst.options[i].selected = false;
	}
	
	return true;
}

{/literal}
//]]>
</script>



<h1>Set Tasks Order: {$date|date_format:$smarty.const.DATE_FORMAT_LONG_SMARTY}, {$user->get_name()}</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="data_frm" onsubmit="return doSubmit ();">
{$form_redir}

<p>Note that this modifies only the relative priority of the tasks. It does <b>NOT</b> affect their start or end time.</p>

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Tasks for the day</td>
		<td>Task control</td>
	</tr>
	</thead>
	<tr>
		<td width="410">
			<select name="tasks_list[]" size="20" style="width: 400px" onchange="showSelectedTask();" multiple>
			{foreach from=$tasks item=task}
				<option value="{$task->id}"># {$task->ticket_id}: {$task->ticket_subject|escape}</option>
			{/foreach}
			</select>
		</td>
		<td nowrap="nowrap">
			<a href="#" onclick="return doMove(-1);">Move up</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="#" onclick="return doMove(1);">Move down</a>
			<p/>
			<a href="#" onclick="return doMoveTo(0);">Move top</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="#" onclick="return doMoveTo(bottom_pos);">Move bottom</a>
			
			<br/><br/><br/>
			
			<table>
				<tr>
					<td colspan="2"><b>Task details:</b></td>
				</tr>
				<tr>
					<td width="80">Ticket #:</td>
					<td><input type="text" name="task_ticket_id" readonly size="12" value="" /></td>
				</tr>
				<tr>
					<td>Hour:</td>
					<td><input type="text" name="task_hour" readonly size="12" value="" /></td>
				</tr>
				<tr>
					<td>Duration:</td>
					<td><input type="text" name="task_duration" readonly size="12" value="" /></td>
				</tr>
				<tr>
					<td>Location:</td>
					<td><input type="text" name="task_location" readonly size="12" value="" /></td>
				</tr>
				<tr>
					<td>Customer:</td>
					<td><input type="text" name="task_customer" readonly size="50" value="" /></td>
				</tr>
			</table>
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Save" class="button" onclick="save_clicked = true; document.forms['data_frm'].elements['tasks_list[]'].multiple=true;"/>
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>
<p/>
