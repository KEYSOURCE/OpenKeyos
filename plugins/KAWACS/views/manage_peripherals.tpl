{assign var="paging_titles" value="KAWACS, Manage Peripherals"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
function do_add_peripheral ()
{
	frm = document.forms['filter']
	elm = frm.elements['add_class_id']
	
	class_id = elm.options[elm.selectedIndex].value
	if (class_id == '')
	{
		alert ('Please select the type of peripheral to add.')
	}
	else
	{
		url = "{/literal}{$add_url}{literal}" + "&class_id=" + class_id
		window.location = url
	}
	
	return false
}
{/literal}
//]>
</script>


<h1>Manage Peripherals</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $customer->id}
				<b>Add peripheral: </b>
				<select name="add_class_id">
					<option value="">[Select type]</option>
					{html_options options=$classes_list}
				</select>
				<a href="{$add_url}" onClick="return do_add_peripheral();">Add&nbsp;&#0187;</a>
			{/if}
		</td>
	</tr>
</table>
<p/>

{if $customer->id}
<h2>AD Printers</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="60">Asset&nbsp;No.</td>
		<td width="40%">Name</td>
		<td width="40%">Customer location</td>
		<td width="20" align="center">Monitor</td>
		<td width="20%" align="right">AD server</td>
	</tr>
	</thead>
	
	{foreach from=$ad_printers item=printer}
	<tr>
        {assign var="p" value="computer_id:"|cat:$printer->computer_id|cat:",nrc:"cat:$printer->nrc}
		<td><a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$printer->asset_no}</a></td>
		<td><a href="{'kerm'|get_link:'ad_printer_view':$p:'template'}">{$printer->name}</a></td>
		<td>
			{if $printer->customer_location}
				{assign var="p" value="id:"|cat:$printer->customer_location->id|cat:",returl:"|cat:$ret_url}
                <a href="{'customer'|get_link:'location_edit':$p:'template'}">
				{foreach from=$printer->customer_location->parents item=parent}
					{$parent->name} &#0187;
				{/foreach}
				{$printer->customer_location->name|escape}</a>
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="center">{if $printer->snmp_enabled}Yes{else}-{/if}</td>
		<td align="right">
			{assign var="computer_id" value=$printer->computer_id}
            {assign var="p" value="id:"|cat:$printer->computer_id}
            <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$printer->computer_id}:&nbsp;{$computers_list.$computer_id}&nbsp;&#0187;</a>
		</td> 
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3">[No AD Printers]</td>
	</tr>
	{/foreach}
	
</table>
<p>


{foreach from=$peripherals_all key=class_id item=peripherals}
	{assign var="class_def" value=$peripherals.0->class_def}
	{assign var="class_id" value=$class_def->id}
    {assign var="p" value="id:"|cat:$customer->id|cat:",class_id:"|cat:$class_id}
	<h2>{$classes_list.$class_id} | <a href="{'kawacs'|get_link:'peripheral_add':$p:'template'}">Add &#0187;</a></h2>
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10">ID</td>
			<td width="{$name_widths.$class_id}%">Name</td>
	
			{foreach from=$class_def->field_defs key=idx item=field_def}
				{if $field_def->in_listings}
					{assign var="field_id" value=$field_def->id}
					<td width="{$display_widths.$class_id.$idx}%">
						{$field_def->name}
					</td>
				{/if}
			{/foreach}
			
			{if $class_def->link_computers}
				<td>Computers</td>
			{/if}
			
			<td>Photo</td>
			<td width="20" align="center">Monitor</td>
			<td> </td>
		</tr>
		</thead>
		
		{foreach from=$peripherals item=peripheral}
		<tr>
            {assign var="p" value="id:"|cat:$peripheral->id}
			<td><a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripheral->id}<a></td>
			<td><a href="{'kawacs'|get_link:'peripheral_edit':$p:'template'}">{$peripheral->name}</a></td>
			
			{foreach from=$class_def->field_defs key=idx item=field_def}
				{if $field_def->in_listings}
				<td>
					{$peripheral->get_formatted_value($idx)|escape|nl2br}
				</td>
				{/if}
			{/foreach}
			
			{if $class_def->link_computers}
			<td nowrap>
				{foreach from=$peripheral->computers item=computer_id}
                    {assign var="p" value="id:"|cat:$computer_id}
                    <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">{$computer_id}:&nbsp;{$computers_list.$computer_id}</a><br>
				{/foreach}
			</td>
			{/if}
			
			<td>
				{foreach from=$peripheral->photos item=photo}
                    {assign var="p" value="id:"|cat:$photo->id|cat:',returl:'|cat:$ret_url}
					<a href="{'customer'|get_link:'customer_photo_view':$p:'template'}">{$photo->subject|escape}</a><br/>
					<a href="{'customer'|get_link:'customer_photo_view':$p:'template'}">{$photo->get_thumb_tag()}</a>
					<br/>
				{foreachelse}
				<font class="light_text">--</font>
				{/foreach}
			</td>
			<td align="center">{if $peripheral->snmp_enabled}Yes{else}-{/if}</td>
			<td nowrap align="right" width="60">
                {assign var="p" value="id:"|cat:$peripheral->id}
				<a href="{'kawacs'|get_link:'peripheral_delete':$p:'template'}">Delete&nbsp;&#0187;</a>
			</td>
		</tr>
		{/foreach}
	</table>
	<p>
{/foreach}


{/if}

<p>
</form>