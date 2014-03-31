{assign var="paging_titles" value="Customers, Manage Fixed Locations, Edit Customer Location, Assign Computers"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer&op=manage_locations"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function selectAllComputers ()
{
	frm = document.forms['frm_t']
	computers_list = frm.elements['assigned_computers[]']
	
	for (i=0; i<computers_list.options.length; i++)
	{
		computers_list.options[i].selected = true
	}
}

function addComputer ()
{
	frm = document.forms['frm_t']
	computers_list = frm.elements['assigned_computers[]']
	available_list = frm.elements['available_computers']
	
	if (available_list.selectedIndex >= 0)
	{
		opt = new Option (available_list.options[available_list.selectedIndex].text, available_list.options[available_list.selectedIndex].value, false, false)
		
		computers_list.options[computers_list.options.length] = opt
		available_list.options[available_list.selectedIndex] = null
	}
}

function removeComputer ()
{
	frm = document.forms['frm_t']
	computers_list = frm.elements['assigned_computers[]']
	available_list = frm.elements['available_computers']
	
	if (computers_list.selectedIndex >= 0)
	{
		opt = new Option (computers_list.options[computers_list.selectedIndex].text, computers_list.options[computers_list.selectedIndex].value, false, false)
		
		available_list.options[available_list.options.length] = opt
		computers_list.options[computers_list.selectedIndex] = null
	}
}
{/literal}
//]]>
</script>


<h1>Customer Location : Assign Computers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t" onSubmit="selectAllComputers(); return true;">
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
		<td width="50%">Assigned computers</td>
		<td width="50%">Available computers</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="assigned_computers[]" size="16" multiple="multiple" style="width: 250px"  ondblclick="removeComputer();">
				{foreach from=$location->computers_list key=computer_id item=computer_name}
				<option value="{$computer_id}">{$computer_name}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<select name="available_computers" size="16" multiple="multiple" style="width: 250px" ondblclick="addComputer();">
				{foreach from=$computers_list key=computer_id item=computer_name}
				<option value="{$computer_id}">{$computer_name}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />


</form>