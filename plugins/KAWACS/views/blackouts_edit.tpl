{assign var="paging_titles" value="KAWACS, Manage Blackouts, Edit Blackouts"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_blackouts"}
{include file="paging.html"}


<script language="JavaScript" src="/javascript/CalendarPopup.js">
</script>

<script language="JavaScript">
var default_date = "{$default_date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}"
var buffer = new Array ()

{literal}

function do_blackout ()
{
	frm = document.forms['frm_t']
	computers = frm.elements['available_computers']
	
	if (computers.selectedIndex >= 0)
	{
		computer_id = computers.options[computers.selectedIndex].value
		computer_row = document.getElementById ('row_'+computer_id)
		computer_row.style.visibility = 'visible'
		computer_row.style.display = ''
		computer_comments_row = document.getElementById ('row_comments_'+computer_id)
		computer_comments_row.style.visibility = 'visible'
		computer_comments_row.style.display = ''
		
		buffer[computer_id] = computers.options[computers.selectedIndex]
		computers.options[computers.selectedIndex] = null
		
	}
	frm.elements['blacked_out['+computer_id+']'].value = 1
	frm.elements['start_date['+computer_id+']'].value = default_date
	frm.elements['end_date['+computer_id+']'].value = ''
}

function remove_blackout (computer_id)
{
	frm = document.forms['frm_t']
	computers = frm.elements['available_computers']
	
	computer_row = document.getElementById ('row_'+computer_id)
	computer_row.style.visibility = 'hidden'
	computer_row.style.display = 'none'
	computer_comments_row = document.getElementById ('row_comments_'+computer_id)
	computer_comments_row.style.visibility = 'hidden'
	computer_comments_row.style.display = 'none'
	
	computers.options[computers.options.length] = buffer[computer_id]	
	frm.elements['blacked_out['+computer_id+']'].value = 0
	
	return false
}


function blackout_all ()
{
	if (confirm('Are you sure you want to blackout all computers for this customer?'))
	{
		frm = document.forms['frm_t']
		computers = frm.elements['available_computers']
		
		for (i=computers.options.length-1; i>=0 ;i--)
		{
			computers.options[i].selected = true
			do_blackout ()
			
		}
	}

	return false
}

function remove_all ()
{
	if (confirm('Are you sure you want to remove all blackouts for this customer?'))
	{
		frm = document.forms['frm_t']
		
		for (i=0; i<buffer.length; i++)
		{
			if (buffer[i])
			{
				if (frm.elements['blacked_out['+i+']'].value == 1)
				{
					remove_blackout (i)
				}
			}
		}
	}
	
	return false;
}

var cal_all_start = new CalendarPopup(); 
cal_all_start.setReturnFunction('setDateStringAllStart'); 
var cal_all_end = new CalendarPopup(); 
cal_all_end.setReturnFunction('setDateStringAllEnd'); 

function showCalendarSelectorAllStart (name_form, name_element, anchor_name)
{
	if (confirm('Are you sure you want to set the same start date for all blackouts?'))
	{
		elname = name_element;
		frm_name = name_form;
		cal_all_start.showCalendar(anchor_name,getDateString());
	}
}

function setDateStringAllStart (y,m,d)
{
	frm = document.forms['frm_t']
		
	for (i=0; i<buffer.length; i++)
	{
		if (buffer[i])
		{
			if (frm.elements['blacked_out['+i+']'].value == 1)
			{
				frm.elements['start_date['+i+']'].value = d+"/"+m+"/"+y;
			}
		}
	}
}

function showCalendarSelectorAllEnd (name_form, name_element, anchor_name)
{
	if (confirm('Are you sure you want to set the same end date for all blackouts?'))
	{
		elname = name_element;
		frm_name = name_form;
		cal_all_end.showCalendar(anchor_name,getDateString());
	}
}

function setDateStringAllEnd (y,m,d)
{
	frm = document.forms['frm_t']
		
	for (i=0; i<buffer.length; i++)
	{
		if (buffer[i])
		{
			if (frm.elements['blacked_out['+i+']'].value == 1)
			{
				frm.elements['end_date['+i+']'].value = d+"/"+m+"/"+y;
			}
		}
	}
}

function clear_all_start_dates ()
{
	if (confirm('Are you sure you want to clear all start dates?'))
	{
		frm = document.forms['frm_t']
		
		for (i=0; i<buffer.length; i++)
		{
			if (buffer[i])
			{
				if (frm.elements['blacked_out['+i+']'].value == 1)
				{
					frm.elements['start_date['+i+']'].value = ''
				}
			}
		}
	}
	
	return false;
}

function clear_all_end_dates ()
{
	if (confirm('Are you sure you want to clear all end dates?'))
	{
		frm = document.forms['frm_t']
		
		for (i=0; i<buffer.length; i++)
		{
			if (buffer[i])
			{
				if (frm.elements['blacked_out['+i+']'].value == 1)
				{
					frm.elements['end_date['+i+']'].value = ''
				}
			}
		}
	}
	
	return false;
}

</script>
{/literal}

<h1>Edit Blackouts: {$customer->name} ({$customer->id})</h1>
<p/>

<font class="error">{$error_msg}</font>
<p/>

[ <a href="" onClick="return remove_all();">Remove all blackouts</a> ]
[ <a HREF="" onClick="showCalendarSelectorAllStart('frm_t', 'start_all', 'anchor_start_all'); return false;" 
name="anchor_start_all" id="anchor_start_all"
>Set all start dates</a> ]
[ <a HREF="" onClick="showCalendarSelectorAllEnd('frm_t', 'start_all', 'anchor_end_all'); return false;" 
name="anchor_end_all" id="anchor_end_all"
>Set all end dates</a> ]
[ <a href="" onClick="return clear_all_start_dates ();">Clear all start dates</a> ] 
[ <a href="" onClick="return clear_all_end_dates ();">Clear all end dates</a> ] 


<form action="" method="POST" name="frm_t"> 
{$form_redir}
<input type="hidden" name="start_all" value="">
<input type="hidden" name="end_all" value="">

<table width="98%" style="border-collapse: separate;">
<tr>
<td width="70%">
	<table class="list" width="100%">
		<thead>
		<tr>
			<td width="10">ID</td>
			<td width="50%">Name</td>
			<td width="25%">Start</td>
			<td width="25%">End</td>
			<td width="40"> </td>
		</tr>
		</thead>
		
		{foreach from=$computers_list key=computer_id item=computer_name}
			{if isset($computers_blackouts.$computer_id)}
				{assign var="is_blackout" value=true}
				{assign var="idx" value=$computers_blackouts.$computer_id}
				{assign var="blackout" value=$blackouts.$idx}
			{else}
				{assign var="is_blackout" value=false}
				{assign var="blackout" value=null}
			{/if}
			
			<tr id="row_{$computer_id}" {if !$is_blackout} style="display:none;" {/if}>
                {assign var="p" value="id:"|cat:$computer_id}
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_id}</a></td>
				<td><a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_name}</a></td>
				<td>
					<input type="hidden" name="blacked_out[{$computer_id}]" value="{if $is_blackout}1{else}0{/if}">
					<input type="text" size="12" name="start_date[{$computer_id}]" 
						value="{if $blackout->start_date}{$blackout->start_date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
					>
					
					<a HREF="#" onClick="showCalendarSelector('frm_t', 'start_date[{$computer_id}]', 'anchor_start_{$computer_id}'); return false;" 
					name="anchor_start_{$computer_id}" id="anchor_start_{$computer_id}"
					><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
				</td>
				<td>
					<input type="text" size="12" name="end_date[{$computer_id}]" 
						value="{if $blackout->end_date}{$blackout->end_date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
					>
					
					<a HREF="#" onClick="showCalendarSelector('frm_t', 'end_date[{$computer_id}]', 'anchor_end_{$computer_id}'); return false;" 
					name="anchor_end_{$computer_id}" id="anchor_end_{$computer_id}"
					><img src="/images/icon_cal.gif" alt="calendar" border=0 style="vertical-align: middle"></a>
				</td>
				
				<td align="right">
					<a href="" onClick="return remove_blackout({$computer_id});">Remove</a>
				</td>
			</tr>
			<tr id="row_comments_{$computer_id}" {if !$is_blackout} style="display:none;" {/if}>
				<td> </td>
				<td colspan="4" nowrap="nowrap">
					Comments:
					<input type="text" size="100" name="comments[{$computer_id}]" value="{$blackout->comments|escape}" />
				</td>
			</tr>
		{/foreach}
	
	</table>
	<p/>

	<input type="submit" name="save" value="Save">
	<input type="submit" name="cancel" value="Close">
	
</td>

<td width="50">&nbsp</td>

<td salign="right">
	<b>Available computers:</b><br/>
	<select name="available_computers" size="20" style="width:200px"
		onDblClick="do_blackout();"
	>
		{foreach from=$computers_list key=computer_id item=computer_name}
			{if isset($computers_blackouts.$computer_id)}
				<script language="JavaScript">
					buffer[{$computer_id}] = new Option ('{$computer_name}', {$computer_id});
				</script>
			{else}
				<option value="{$computer_id}">{$computer_name}</option>
			{/if}
		{/foreach}
	</select>
	<p/>
	<input type="submit" onClick="return blackout_all();" value="Blackout all">
</td>
</tr>
</table>

</form>

<p/>

