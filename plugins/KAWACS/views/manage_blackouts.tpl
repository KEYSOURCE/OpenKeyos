{assign var="paging_titles" value="KAWACS, Manage Blackouts"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

{literal}
<script language="JavaScript">

function checkEdit ()
{
	frm = document.forms['filter']
	elm = frm.elements['filter[customer_id]']
	customer_id = elm.options[elm.selectedIndex].value
	
	if (customer_id == '')
	{
		alert ('Please select first the customer for which to edit the blackouts');
	}
	else
	{
		window.location = '/kawacs/blackouts_edit?customer_id=' + customer_id
	}
	
	return false
}

</script>
{/literal}


<h1>Manage Blackouts & Ignored Computers</h1>
<p/>
<font class="error">{$error_msg}</font>
<p/>

<form action="" method="POST" name="filter"> 
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			<select name="filter[customer_id]" onChange="document.forms['filter'].submit();">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		<td align="right">
			<a href="" onClick="return checkEdit();">Edit blackouts &amp; ignored computers &#0187;</a>
		</td>
	</tr>
</table>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="30%">Customer</td>
		<td width="30%" colspan="2">Computer</td>
		<td width="10%">Start</td>
		<td width="10%">End</td>
		<td width="5%" align="center">Active</td>
		<td width="15%"> </td>
	</tr>
	</thead>
	
	{foreach from=$blackouts item=blackout}
	{assign var="computer_id" value=$blackout->computer_id}
	{assign var='customer_id' value=$customers_computers_id.$computer_id}
	<tr>
		<td>
            {assign var="p" value="id:"|cat:$customer->id}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$all_customers_list.$customer_id}</a>
		</td>
		<td width="10">
            {assign var="p" value="id:"|cat:$computer->id}
			<a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$blackout->computer_id}:</a>
		</td>
		<td width="30%">
            {assign var="p" value="id:"|cat:$computer->id}
			<a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computers_list.$computer_id}</a>
			{if $blackout->comments}
			<br/><i>{$blackout->comments|escape}</i>
			{/if}
		</td>
		<td>
			{if $blackout->start_date}
				{$blackout->start_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}
			{else}
				-
			{/if}
		</td>
		<td>
			{if $blackout->end_date}
				{$blackout->end_date|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
			{else}
				-
			{/if}
		</td>
		<td align="center">
			{if $blackout->is_active()}
				<b>Y</b>
			{else}
				-
			{/if}
		</td>
		<td align="right">
            {assign var="p" value="customer_id:"|cat:$customer->id}
			<a href="{'kawacs'|get_link:'blackouts_edit':$p:'template'}">Edit</a> |
            {assign var="p" value="computer_id:"|cat:$computer->id}
			<a href="{'kawacs'|get_link:'blackouts_remove':$p:'template'}"
				onClick="return confirm('Are you sure you want to remove this blackout?');"
			>Remove</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No blackouts defined]</td>
	</tr>
	{/foreach}
</table>
</form>
<p/>

<b>NOTES:</b> 
<ul>
<li>If an end date is defined for a blackout, the blackout will be automatically removed after that date passes.</li>
<li>A blackout is considered active if:
	<ul>
		<li>The start and end dates are not defined</li>
		<li>The start date is defined in the past</li>
		<li>The end date is defined in the future</li>
	</ul>
</li>
</ul>
<p/>