{assign var="paging_titles" value="Customers, Manage Fixed Locations, Edit Customer Location, Assign Peripherals"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function selectAllPeripherals ()
{
	frm = document.forms['frm_t']
	peripherals_list = frm.elements['assigned_peripherals[]']
	
	for (i=0; i<peripherals_list.options.length; i++)
	{
		peripherals_list.options[i].selected = true
	}
}

function addPeripheral ()
{
	frm = document.forms['frm_t']
	peripherals_list = frm.elements['assigned_peripherals[]']
	available_list = frm.elements['available_peripherals']
	
	if (available_list.selectedIndex >= 0)
	{
		opt = new Option (available_list.options[available_list.selectedIndex].text, available_list.options[available_list.selectedIndex].value, false, false)
		
		peripherals_list.options[peripherals_list.options.length] = opt
		available_list.options[available_list.selectedIndex] = null
	}
}

function removePeripheral ()
{
	frm = document.forms['frm_t']
	peripherals_list = frm.elements['assigned_peripherals[]']
	available_list = frm.elements['available_peripherals']
	
	if (peripherals_list.selectedIndex >= 0)
	{
		opt = new Option (peripherals_list.options[peripherals_list.selectedIndex].text, peripherals_list.options[peripherals_list.selectedIndex].value, false, false)
		
		available_list.options[available_list.options.length] = opt
		peripherals_list.options[peripherals_list.selectedIndex] = null
	}
}
{/literal}
//]]>
</script>


<h1>Customer Location : Assign Peripherals</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t" onSubmit="selectAllPeripherals(); return true;">
{$form_redir}


<table class="list" width="95%">
	<thead>
	<tr>
		<td width="15%">Customer:</td>
		<td width="85%" class="post_highlight">
			{assign var="customer_id" value=$location->customer_id}
			{$customers_list.$customer_id} (# {$customer_id})
		</td>
	</tr>
	<tr>
		<td>Location:</td>
		<td class="post_highlight">
			{foreach from=$location->parents item=parent}
				{$parent->name|escape} &#0187;
			{/foreach}
			{$location->name|escape}
		</td>
	</tr>
	</thead>
</table>
<p/>

<table class="list" width="95%">
	<thead>
	<tr>
		<td width="50%">Assigned peripherals</td>
		<td width="50%">Available peripherals</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="assigned_peripherals[]" size="16" multiple="multiple" style="width: 250px"  ondblclick="removePeripheral();">
				{foreach from=$location->peripherals_list key=peripheral_id item=peripheral_name}
				<option value="{$peripheral_id}">{$peripheral_name}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<select name="available_peripherals" size="16" multiple="multiple" style="width: 250px" ondblclick="addPeripheral();">
				{foreach from=$peripherals_list key=peripheral_id item=peripheral_name}
				<option value="{$peripheral_id}">{$peripheral_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />


</form>