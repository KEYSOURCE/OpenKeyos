{assign var="paging_titles" value="KALM, Manage Licenses, Edit License"}
{assign var="paging_urls" value="/?cl=kalm, /?cl=kalm&op=manage_licenses"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var freeware_id = {$smarty.const.LIC_TYPE_FREEWARE};
var cal_id = {$smarty.const.LIC_TYPE_CLIENT};
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
	
	elm = document.getElementById ('cal_used_input');
	if (current_type == cal_id)
	{
		elm.style.display = "";
	}
	else
	{
		elm.style.display = "none";
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

<h1>Edit License</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="license_frm">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		<td colspan="3">Customer: {$customer->name}</td>
	</tr>
	</thead>
	
	<tr>
		<td width="20%">Software: </td>
		<td width="70%">
			<select name="license[software_id]">
				<option value="">[Select software]</option>
				{html_options options=$softwares selected=$license->software_id}
			</select>
		</td>
		<td width="10%"> </td>
	</tr>
	<tr>
		<td>Licensing type: </td>
		<td colspan="2">
			<select name="license[license_type]" onchange="check_lic_type ();">
				{html_options options=$LIC_TYPES_NAMES selected=$license->license_type}
			</select>
		</td>
	</tr>
	<tr>
		<td>Licenses: </td>
		<td colspan="2">
			<div id="licenses_input" style="display:inline;">
				<input type="text" name="license[licenses]" value="{$license->licenses}" size="5">
				&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
			
			<input type="checkbox" name="unlimited_lics" value="1" class="checkbox"
			{if $license->licenses==-1} checked {/if}
			onclick="set_unlimited (this.checked);" onchange="set_unlimited (this.checked);"> Unlimited
		</td>
	</tr>
	
	<tr id="cal_used_input" style="display: none;">
		<td>Used licenses: </td>
		<td colspan="2">
			<input type="text" name="license[used]" value="{$license->used}" size="5">
			- For "per client" (CAL) licenses only.
		</td>
	</tr>
	
	<tr>
		<td>No notifications: </td>
		<td colspan="2">
			<input type="checkbox" name="license[no_notifications]" value="1" class="checkbox"
			{if $license->no_notifications} checked {/if} />
			<i>If set, no notifications will be raised if licenses are exceeded. Applies to all packages of the same type for this customer.</i>
		</td>
	</tr>
	<tr>
		<td>Issue date: </td>
		<td colspan="2">
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
		</td colspan="2">
	</tr>
	
	<tr>
		<td>Expiration date: </td>
		<td colspan="2">
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
		<td colspan="2"><textarea name="license[comments]" rows="4" cols="60">{$license->comments}</textarea></td>
	</tr>

	
	<tr class="head">
		<td>Serial numbers</td>
		<td colspan="2"><a href="/?cl=kalm&amp;op=license_sn_add&amp;license_id={$license->id}">Add &#0187;</a></td>
	</tr>
	
	{foreach from=$license->serials item=sn}
	<tr>
		<td> </td>
		<td>
			<a href="/?cl=kalm&amp;op=license_sn_edit&amp;id={$sn->id}">{$sn->sn}</a>
			{if $sn->comments}
				<br/>
				{$sn->comments|escape|nl2br}
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=kalm&amp;op=license_sn_delete&amp;id={$sn->id}"
				onclick="return confirm('Are you sure you want to delete this serial number?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td> </td>
		<td class="light_text" colspan="2">[No serial numbers entered]</td>
	</tr>
	{/foreach}
	
	<tr class="head">
		<td>Files</td>
		<td colspan="2"><a href="/?cl=kalm&amp;op=license_file_add&amp;license_id={$license->id}">Add &#0187;</a></td>
	</tr>
	
	{foreach from=$license->files item=file}
	<tr>
		<td> </td>
		<td>
			<a href="/?cl=kalm&amp;op=license_file_open&amp;id={$file->id}">{$file->original_filename}</a>
			{if $file->comments}
				<br/>{$file->comments|escape|nl2br}
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=kalm&amp;op=license_file_edit&amp;id={$file->id}">Edit &#0187;</a>
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="/?cl=kalm&amp;op=license_file_delete&amp;id={$file->id}"
				onclick="return confirm('Are you sure you want to delete this file?')"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td> </td>
		<td class="light_text" colspan="2">[No files]</td>
	</tr>
	{/foreach}
</table>

<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

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
