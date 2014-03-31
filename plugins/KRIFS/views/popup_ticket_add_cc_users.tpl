
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax_users.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

window.resizeTo (500, 350);
var parent_window = window.opener;

// This will store the temporary list of CC users in the pop-up window
var selected_users = parent_window.getCCUsers ();

// This will store all the available users
var all_users = new UserInfosList ();
{foreach from=$all_users key=user_id item=user}
	all_users.appendUser ({$user->id}, '{$user->get_name()}', {if $user->is_customer_user()}true{else}false{/if});
{/foreach}

{literal}

// Updates the list of available users
function updateAvailableUsers ()
{
	var frm = document.forms['frm_t'];
	var lst_available_users = frm.elements['list_available_users'];
	var obj = null;
	
	for (var i=lst_available_users.options.length-1; i>=0; i--) lst_available_users.options[i] = null;
	for (i=0; i<all_users.users.length; i++)
	{
		obj = all_users.users[i];
		lst_available_users.options[i] = new Option (obj.user_name, obj.user_id);
	}
}

// Updates the list of selected objects. This should be called anytime the selection gets changed
function updateSelectedUsers ()
{
	var frm = document.forms['frm_t'];
	var lst_users = frm.elements['list_users'];
	for (var i=lst_users.options.length-1; i>=0; i--) lst_users.options[i] = null;
	
	for (i = 0; i<selected_users.users.length; i++)
	{
		obj = selected_users.users[i];
		lst_users.options[i] = new Option (obj.user_name, obj.user_id);
	}
}

// Adds an user to the selected list
function addUser ()
{
	var frm = document.forms['frm_t'];
	var lst_available_users = frm.elements['list_available_users'];
	var obj = all_users.users[lst_available_users.selectedIndex];
	
	// Check if the object has not been selected already
	if (selected_users.hasUser (obj.user_id)) alert ('This user has already been selected');
	else 
	{
		selected_users.appendUser (obj.user_id, obj.user_name, obj.is_customer_user);
		updateSelectedUsers ();
	}
}

// Removes a user from the selected list
function removeUser ()
{
	var frm = document.forms['frm_t'];
	var lst_users = frm.elements['list_users'];
	selected_users.removeUserByIdx (lst_users.selectedIndex);
	updateSelectedUsers ();
}

// "Saves" the selected users by passing them back to the caller window
function saveUsers ()
{
	var emls = document.getElementById('list_emails');
	parent_window.setCCUsers (selected_users, emls.value);
	window.close ();
}

// Deletes all selected users
function clearAllUsers ()
{
	selected_users = new UserInfosList ();
	updateSelectedUsers ();
	document.getElementById('list_emails').value="";
}

{/literal}

//]]>
</script>

<div style="disply:block; padding: 10px;">
<form action="" method="POST" name="frm_t">

<h2>Select CC Users</h2>

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="80" colspan="2">Customer: &nbsp;&nbsp;&nbsp;#{$customer->id}: {$customer->name|escape}</td>
	</tr>
	</thead>
	
	{if count($all_users) > 0}
	<tr>
		<td>
			Available users:<br/>
			<select name="list_available_users" size="12" style="width: 200px;" ondblclick="addUser();">
			</select>
		</td>
		<td class="post_highlight">
			Selected users:<br/>
			<select name="list_users" size="12" style="width: 200px;" ondblclick="removeUser();">
			</select>
		</td>
	</tr>
	{else}
	<tr>
		<td colspan="2" class="light_text">[No users available]</td>
	</tr>
	{/if}
</table>
<p />
<table class="list">
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
			<textarea id="list_emails" name="list_emails" rows="3" cols="100" align="left"></textarea>
		</td>
	</tr>
</table>
<p/>

{if count($all_users) > 0}
<input type="submit" name="save" value="Set CC Users" onclick="saveUsers(); return false;" />
{/if}
<input type="submit" name="cancel" value="Cancel" onclick="window.close(); return false;" />
&nbsp;&nbsp;&nbsp;
{if count($all_users) > 0}
<input type="submit" name="clear_all" value="Clear all" onclick="clearAllUsers(); return false;" />
{/if}

</form>
</div>

{if count($all_users) > 0}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
updateAvailableUsers();
updateSelectedUsers();
//]]>
</script>
{/if}