{assign var="paging_titles" value="KLARA, Manage Access Phones, Edit Access Phone"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access_phones"}
{include file="paging.html"}

<script language="JavaScript">

var dev_type_computer = {$smarty.const.PHONE_ACCESS_DEV_COMPUTER}
var dev_type_peripheral = {$smarty.const.PHONE_ACCESS_DEV_PERIPHERAL} 

{literal}
function check_sel_device ()
{
	frm = document.forms['add_frm']
	computers_list = document.getElementById ('lst_computers');
	peripherals_list = document.getElementById ('lst_peripherals');
	lst = frm.elements['access_phone[device_type]']
	
	dev_type = lst.options[lst.selectedIndex].value
	computers_list.style.display = 'none'
	peripherals_list.style.display = 'none'
	
	if (dev_type == dev_type_computer)
	{
		computers_list.style.display = ''
	}
	if (dev_type == dev_type_peripheral)
	{
		peripherals_list.style.display = ''
	}
}
{/literal}

</script>



<h1>Edit Access Phone</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td>Customer:</td>
		<td>{$customer->name} ({$customer->id})</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Phone number:</td>
		<td>
			<input type="text" name="access_phone[phone]" value="{$access_phone->phone}" size="20"/>
		</td>
	</tr>
	<tr>
		<td>Connected device:</td>
		<td>
			<select name="access_phone[device_type]" onChange="check_sel_device()">
				<option value="">[Select]</option>
				{html_options options=$PHONE_ACCESS_DEVICES selected=$access_phone->device_type}
			</select>
			
			<select name="computer_id" style="display:none" id="lst_computers">
				<option value="">[Select]</option>
				{html_options options=$computers_list selected=$access_phone->object_id}
			</select>
			
			<select name="peripheral_id" style="display:none" id="lst_peripherals">
				<option value="">[Select]</option>
				{html_options options=$peripherals_list selected=$access_phone->object_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Login name:</td>
		<td>
			<input type="text" name="access_phone[login]" value="{$access_phone->login}"/>
		</td>
	</tr>
	<tr>
		<td>Password:</td>
		<td>
			<input type="text" name="access_phone[password]" value="{$access_phone->password}"/>
		</td>
	</tr>
	<tr>
		<td>Comments:</td>
		<td>
			<textarea name="access_phone[comments]" rows="6" cols="50">{$access_phone->comments|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Close"/>
</form>

<script language="JavaScript">
check_sel_device ();
</script>