{assign var="paging_titles" value="KALM, Manage Licenses, Add License"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript">
//<![CDATA[

var freeware_id = {$smarty.const.LIC_TYPE_FREEWARE};
{literal}

function check_lic_type ()
{
	frm = document.forms['license_frm'];
	lic_type_list = frm.elements['license[license_type]'];
	current_type = lic_type_list.options[lic_type_list.selectedIndex].value;
	
	if (current_type == freeware_id)
	{
		frm.elements["license[licenses]"].value = -1;
	}
	else
	{
		if (frm.elements["license[licenses]"].value == -1)
		{
			frm.elements["license[licenses]"].value = "";
		}
	}
	
	check_unlimited ();
}

function check_unlimited ()
{
	frm = document.forms['license_frm'];
	
	if (frm.elements["license[licenses]"].value == -1)
	{
		elm = document.getElementById ('licenses_input');
		elm.style.display = "none";
		frm.elements['unlimited_lics'].checked = true;
	}
	else
	{
		elm = document.getElementById ('licenses_input');
		elm.style.display = "inline";
		frm.elements['unlimited_lics'].checked = false;
	}
}


function set_unlimited (is_unlimited)
{
	frm = document.forms['license_frm'];
	
	lic_type_list = frm.elements['license[license_type]'];
	current_type = lic_type_list.options[lic_type_list.selectedIndex].value;
	
	if (is_unlimited)
	{
		frm.elements["license[licenses]"].value = -1;
	}
	else
	{
		if (current_type == freeware_id)
		{
			alert ('Freeware software can only have unlimited licenses.');
		}
		else
		{
			frm.elements["license[licenses]"].value = "";
		}
	}
	
	check_unlimited ();
}

{/literal}
//]]>
</script>

<h1>Add License</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="license_frm">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="2">Customer: {$customer->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Software: </td>
		<td>
			<select name="license[software_id]">
				<option value="">[Select software]</option>
				{html_options options=$softwares selected=$license->software_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Licensing type: </td>
		<td>
			<select name="license[license_type]" onchange="check_lic_type ();">
				{html_options options=$LIC_TYPES_NAMES selected=$license->license_type}
			</select>
		</td>
	</tr>
	<tr>
		<td>Licenses: </td>
		<td>
			<div id="licenses_input" style="display:inline;">
				<input type="text" name="license[licenses]" value="{$license->licenses}" size="5">
				&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
			
			<input type="checkbox" name="unlimited_lics" value="1" class="checkbox" 
				{if $license->licenses==-1} checked {/if}
			onclick="set_unlimited (this.checked);" onchange="set_unlimited (this.checked);"> Unlimited
		</td>
	</tr>
	<tr>
		<td>Issue date: </td>
		<td>
			{if $license->issue_date}
				{assign var="time" value=$license->issue_date}
			{else}
				{assign var="time" value="0000-00-00"}
			{/if}

			{html_select_date 
				field_array="license[issue_date]"
				start_year="-10"
				end_year="+10" 
				time=$time
				year_empty="--" month_empty="--" day_empty="--"
			}
		</td>
	</tr>
	
	<tr>
		<td>Expiration date: </td>
		<td>
			{if $license->exp_date}
				{assign var="time" value=$license->exp_date}
			{else}
				{assign var="time" value="0000-00-00"}
			{/if}

			{html_select_date 
				field_array="license[exp_date]"
				start_year="-10"
				end_year="+10" 
				time=$time
				year_empty="--" month_empty="--" day_empty="--"
			}
		</td>
	</tr>
	
	<tr>
		<td>Comments: </td>
		<td><textarea name="license[comments]" rows="4" cols="60">{$license->comments}</textarea></td>
	
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">

</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{if $license->licenses == -1}
	 set_unlimited (true);
{else}
	check_lic_type ();
{/if}
//]]>
</script>
