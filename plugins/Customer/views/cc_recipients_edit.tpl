{assign var="paging_titles" value="Customers, Manage CC Recipients, Edit CC Recipients"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_cc_recipients"}
{include file="paging.html"}

<script language="JavaScript">
//<![CDATA[

{literal}

// Adds a user to the recipients list
function addUser ()
{
	var frm = document.forms['cc_frm'];
	var list_users = frm.elements['available_users'];
	var list_recipients = frm.elements['cc_recipients[]'];
	
	if (list_users.selectedIndex >= 0)
	{
		opt = new Option (list_users.options[list_users.selectedIndex].text, list_users.options[list_users.selectedIndex].value);
		list_recipients.options[list_recipients.options.length] = opt;
		list_users.options[list_users.selectedIndex] = null;
	}
}

// Removes a user from the recipients list
function removeUser ()
{
	var frm = document.forms['cc_frm'];
	var list_users = frm.elements['available_users'];
	var list_recipients = frm.elements['cc_recipients[]'];
	
	if (list_recipients.selectedIndex >= 0)
	{
		opt = new Option (list_recipients.options[list_recipients.selectedIndex].text, list_recipients.options[list_recipients.selectedIndex].value);
		list_users.options[list_users.options.length] = opt;
		list_recipients.options[list_recipients.selectedIndex] = null;
	}
}

// Upon form submission ensures that all options in the recipients list are selected
function selectAllRecipients ()
{
	var frm = document.forms['cc_frm'];
	var list_recipients = frm.elements['cc_recipients[]'];
	for (var i=0; i<list_recipients.options.length; i++) list_recipients.options[i].selected = true;
	
	return true;
}
{/literal}
//]]>
</script>

<h1>Edit CC Recipients</h1>

<p class="error">{$error_msg}</p>

<p>Specify below the users who should be added by default as CC recipients
to the new tickets for this customer.</p>

<form action="" method="POST" name="cc_frm" onSubmit="return selectAllRecipients();">
{$form_redir}
<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2">Customer: &nbsp;&nbsp;&nbsp;#{$customer->id}: {$customer->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			Available users:<br/>
			<select name="available_users" multiple size="18" style="width: 250px;" ondblclick="addUser();">
				{foreach from=$all_users key=user_id item=user_name}
				{if !in_array($user_id,$recipients_ids)}
				<option value="{$user_id}">{$user_name|escape}</option>
				{/if}
				{/foreach}
			</select>
		</td>
		<td class="post_highlight">
			Selected recipients:<br/>
			<select name="cc_recipients[]" multiple size="18" style="width: 250px;" ondblclick="removeUser();">
				{foreach from=$recipients item=user}
				<option value="{$user->id}">{$user->get_name()|escape}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>
<p/>
<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />

</form>