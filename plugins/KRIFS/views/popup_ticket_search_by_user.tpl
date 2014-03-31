
<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>
<script language="JavaScript" src="/javascript/ajax_krifs.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
//<![CDATA[

window.resizeTo (750, 500);
var parent_window = window.opener;

var obj_computer_class_id = {$obj_computer_class_id};
var obj_computer_class_name = '{$obj_computer_class_name}';
var obj_user_class_id = {$obj_user_class_id};
var obj_user_class_name = '{$obj_user_class_name}';

{literal}

// Sends the selected computer to the parent window 
function doSelectComputer (computer_id, computer_name)
{
	parent_window.appendLinkedObject (obj_computer_class_id, obj_computer_class_name, computer_id, computer_name);
	return false;
}

// Sends the selected user to the parent window 
function doSelectUser (user_id, user_name)
{
	parent_window.appendLinkedObject (obj_user_class_id, obj_user_class_name, user_id, user_name);
	return false;
}

{/literal}

//]]>
</script>

<div style="disply:block; padding: 10px;">
<form action="" method="POST" name="frm_t">

<h1>Search by user: {$customer->name|escape}</h1>

<p>
Below you have the list of AD users for this customer and the 
computers on which they logged on recently.<br/>
Click on a computer name or on a user name to add them to the ticket's linked objects.<br/>
When you finished adding all needed objects, simply close this window.
</p>

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="10%">Login</td>
		<td width="30%">Display name</td>
		<td width="30%">E-mail</td>
		<td width="30%">Computers</td>
	</tr>
	</thead>
	
	{foreach from=$ad_users key=idx item=ad_user}
	<tr>
		<td>{$ad_user->sam_account_name|escape}</td>
		<td>
			<a href="#" onclick="return doSelectUser('{$ad_user->computer_id}_{$ad_user->nrc}', '{$ad_user->sam_account_name} ({$ad_user->display_name})')">{$ad_user->display_name|escape}</a>
		</td>
		<td>{$ad_user->email|escape}</td>
		<td nowrap="nowap">
			{if count($used_computers.$idx) > 0}
				{foreach from=$used_computers.$idx key=computer_id item=timestamp}
				<a href="#" onclick="return doSelectComputer ({$computer_id}, '{$computers_list.$computer_id}');">{$computers_list.$computer_id|escape}</a>
				({$timestamp|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY})
				<br/>
				{/foreach}
			{else}
				<font class="light_text">[No computers found]</font>
			{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No AD users found]</td>
	</tr>
	{/foreach}
</table>
<p/>


<input type="submit" name="cancel" value="Close" onclick="window.close(); return false;" />
</form>
</div>
