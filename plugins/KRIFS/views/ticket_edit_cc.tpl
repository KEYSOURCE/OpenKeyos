{assign var="ticket_id" value=$ticket->id}
{assign var="paging_titles" value="KRIFS, Ticket, Edit CC List"}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit/"|cat:$ticket_id}
{include file="paging.html"}

{literal}
<script language="JavaScript">

function selectAllMembers ()
{
	frm = document.forms['cc_frm']
	mbr_list = frm.elements['ticket[cc_list][]']
	
	for (i=0; i<mbr_list.options.length; i++)
	{
		mbr_list.options[i].selected = true
	}
}

function addMember ()
{
	frm = document.forms['cc_frm']
	mbr_list = frm.elements['ticket[cc_list][]']
	users_list = frm.elements['available_users']
	
	if (users_list.selectedIndex >= 0)
	{
		opt = new Option (users_list.options[users_list.selectedIndex].text, users_list.options[users_list.selectedIndex].value, false, false)
		
		mbr_list.options[mbr_list.options.length] = opt
		users_list.options[users_list.selectedIndex] = null
	}
}

function removeMember ()
{
	frm = document.forms['cc_frm']
	mbr_list = frm.elements['ticket[cc_list][]']
	users_list = frm.elements['available_users']
	
	if (mbr_list.selectedIndex >= 0)
	{
		opt = new Option (mbr_list.options[mbr_list.selectedIndex].text, mbr_list.options[mbr_list.selectedIndex].value, false, false)
		
		users_list.options[users_list.options.length] = opt
		mbr_list.options[mbr_list.selectedIndex] = null
	}
}

</script>
{/literal}




<h1>Edit CC List : Ticket # {$ticket->id}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

Please specify below the users who should be in the CC list to receive notifications
about this ticket:
<p>

<form action="" method="POST" name="cc_frm" onSubmit="selectAllMembers(); return true;">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td>Current CC list</td>
		<td>Available recipients</td>
	</tr>
	</thead>
	
	<tr>
		<td width="50%">
			<select name="ticket[cc_list][]" size=20 style="width: 200px;" multiple onDblClick="removeMember();">
				{foreach from=$ticket->cc_list item=user_id}
					<option value="{$user_id}">{$all_users.$user_id}</option>
				{/foreach}
			</select>
		</td>
		
		<td width="50%">
			<select name="available_users" size=20  style="width: 200px;" multiple onDblClick="addMember();">
				{foreach from=$users key=user_id item=user_name}
					{if !in_array($user_id, $ticket->cc_list)}
						<option value="{$user_id}">{$user_name}</option>
					{/if}
				{/foreach}
					
				{if $customer_users}
					{foreach from=$customer_users key=user_id item=user_name}
						{if !in_array($user_id, $ticket->cc_list)}
							<option value="{$user_id}">{$user_name}</option>
						{/if}
					{/foreach}
				{/if}
			</select>
		
		</td>
	
	</tr>
</table>
<p />
<table class="list" width="60%">
	<thead>
		<tr>
			<td>Manually add email addresses</td>
		</tr>
	</thead>
	<tr>
		<td>If you need to add more email addresses, separate them with comma</td>
	</tr>
	<tr>
		<td width="100%">
		{assign var="emails" value=$ticket->cc_manual_list}
			<textarea name="ticket[cc_manual_list]" rows="3" cols="100" align="left">{foreach from=$emails item="eml"}{$eml};{/foreach}</textarea>
		</td>
	</tr>
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>
