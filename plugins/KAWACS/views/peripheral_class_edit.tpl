{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Edit Class"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript">
//<![CDATA[

var class_id = {$peripheral_class->id};
var returl = "{$ret_url}";

{literal}

function check_div (elm_name, div_id)
{
	frm = document.forms['frm_peripheral']
	elm = frm.elements[elm_name]
	div = document.getElementById (div_id)
	
	if (elm.checked)
	{
		div.style.display = 'inline'
	}
	else
	{
		div.style.display = 'none'
	}
}

function doAddProfile ()
{
	frm = document.forms['frm_peripheral'];
	elm_profiles = frm.elements['profiles'];
	
	if (elm_profiles.selectedIndex == 0) 
	{
		alert ('Please select a profile from the list first.');
	}
	else
	{
		window.location = '/?cl=kawacs&op=peripheral_class_profile&class_id=' + class_id + '&profile_id=' + elm_profiles.options[elm_profiles.selectedIndex].value + '&returl='+returl;
		
	}
	return false;
}
{/literal}

//]]>
</script>


<h1>Edit Peripheral Class</h1>
<p>

<font class="error">{$error_msg}</font>
<p>

<form action="" method="post" name="frm_peripheral">
{$form_redir}

<p>
<table width="98%" class="list">
	<thead>
	<tr>
		<td width="20%">ID:</td>
		<td>{$peripheral_class->id}</td>
	</tr>
	</thead>
	<tr>
		<td><b>Class name:</b></td>
		<td><input type="text" name="peripheral_class[name]" size="40" value="{$peripheral_class->name}"></td>
	</tr>
	<tr>
		<td><b>Name relative display width:</b></td>
		<td>
			<select name="peripheral_class[name_width]">
				{html_options output=$peripheral_class->width_options values=$peripheral_class->width_options selected=$peripheral_class->name_width}
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" name="peripheral_class[link_computers]" {if $peripheral_class->link_computers}checked{/if}>
			Link to computers
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="peripheral_class[use_warranty]" 
				{if $peripheral_class->use_warranty}checked{/if}
				onClick="check_div (this.name, 'div_warranty_fields')";
			>
			Use warranties
		</td>
		<td>
			<div id="div_warranty_fields" style="display: inline">
				Warranty start:
				<select name="peripheral_class[warranty_start_field]">
					<option value="">[Select field]</option>
					{html_options options=$date_fields selected=$peripheral_class->warranty_start_field}
				</select>
				&nbsp;&nbsp;&nbsp;
				Warranty end:
				<select name="peripheral_class[warranty_end_field]">
					<option value="">[Select field]</option>
					{html_options options=$date_fields selected=$peripheral_class->warranty_end_field}
				</select>
				<br/>
				Service package:
				<select name="peripheral_class[warranty_service_package_field]">
					<option value="">[Select field]</option>
					{html_options options=$int_fields selected=$peripheral_class->warranty_service_package_field}
				</select>
				&nbsp;&nbsp;&nbsp;
				Service level:
				<select name="peripheral_class[warranty_service_level_field]">
					<option value="">[Select field]</option>
					{html_options options=$int_fields selected=$peripheral_class->warranty_service_level_field}
				</select>
				<br/>
				Contract number:
				<select name="peripheral_class[warranty_contract_number_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->warranty_contract_number_field}
				</select>
				&nbsp;&nbsp;&nbsp;
				HW product ID:
				<select name="peripheral_class[warranty_hw_product_id_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->warranty_hw_product_id_field}
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="peripheral_class[use_sn]" 
				{if $peripheral_class->use_sn}checked{/if}
				onClick="check_div (this.name, 'div_sn_field')";
			>
			Use serial numbers
		</td>
		<td>
			
			<div id="div_sn_field" style="display: inline">
				Serial number:
				<select name="peripheral_class[sn_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->sn_field}
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="peripheral_class[use_web_access]" 
				{if $peripheral_class->use_web_access}checked{/if}
				onClick="check_div (this.name, 'div_web_access_field')";
			>
			Web access
		</td>
		<td>
			
			<div id="div_web_access_field" style="display: inline">
				URL:
				<select name="peripheral_class[web_access_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->web_access_field}
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="peripheral_class[use_net_access]" 
				{if $peripheral_class->use_net_access}checked{/if}
				onclick="check_div (this.name, 'div_net_access_field')";
			>
			Network access
		</td>
		<td>
			
			<div id="div_net_access_field" style="display: inline;">
				IP:
				<select name="peripheral_class[net_access_ip_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->net_access_ip_field}
				</select>
				&nbsp;&nbsp;&nbsp;
				Port:
				<select name="peripheral_class[net_access_port_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->net_access_port_field}
				</select>
				<br/>
				Login:
				<select name="peripheral_class[net_access_login_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->net_access_login_field}
				</select>
				&nbsp;&nbsp;&nbsp;
				Password:
				<select name="peripheral_class[net_access_password_field]">
					<option value="">[Select field]</option>
					{html_options options=$string_fields selected=$peripheral_class->net_access_password_field}
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="peripheral_class[use_snmp]"
				{if $peripheral_class->use_snmp}checked{/if} 
				onclick="check_div (this.name, 'div_snmp')";
			/>
			Use SNMP
		</td>
		<td>
			<div id="div_snmp">
				Here you can specify what peripherals monitoring profiles can be assigned to 
				peripherals of this class and how the monitoring items are mapped to classe's fields.
				<table class="list" width="450">
				<thead>
				<tr>
					<td>Profile</td>
					<td>Items associations</td>
					<td align="right" nowrap="nowrap">
						<select name="profiles">
							<option value="">[Select profile]</option>
							{html_options options=$profiles_list}
						</select>
						<a href="#" onclick="return doAddProfile();">Associations &#0187;</a>
					</td>
				</tr>
				</thead>
				
				{foreach from=$peripheral_class->profiles item=profile}
					<tr>
						<td>{$profile->name|escape}</td>
						<td>
							{assign var="profile_id" value=$profile->id}
							<a href="/?cl=kawacs&amp;op=peripheral_class_profile&amp;class_id={$peripheral_class->id}&amp;profile_id={$profile->id}&amp;returl={$ret_url}">{$peripheral_class->profiles_fields_ids.$profile_id|@count} fields: View &#0187;</a>
						</td>
						<td align="right">
							<a href="/?cl=kawacs&amp;op=peripheral_class_profile_remove&amp;class_id={$peripheral_class->id}&amp;profile_id={$profile->id}&amp;returl={$ret_url}"
							onclick="return confirm('Are you really sure you want to remove all associations with this monitor profile?');"
							>Remove</a>
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="3" class="light_text">[No associated monitor profiles]</td>
					</tr>
				{/foreach}
				</table>
			</div>
		</td>
	</tr>
</table>
<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">
<p>

<h2>Fields</h2>
<p>
<a href="/?cl=kawacs&op=peripheral_field_add&class_id={$peripheral_class->id}">Add field &#0187;</a>
&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
<a href="/?cl=kawacs&op=peripheral_field_order&id={$peripheral_class->id}">Change fields order &#0187;</a>
<p>

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="10">ID</td>
		<td>Name</td>
		<td>Type</td>
		<td>In listings</td>
		<td>In reports</td>
		<td>Relative width</td>
		<td> </td>
	</tr>
	</thead>

	
	{foreach from=$peripheral_class->field_defs item=field_def}
	<tr>
		<td><a href="/?cl=kawacs&op=peripheral_field_edit&id={$field_def->id}">{$field_def->id}</a></td>
		<td><a href="/?cl=kawacs&op=peripheral_field_edit&id={$field_def->id}">{$field_def->name}</a></td>
		<td>
			{assign var="type_id" value=$field_def->type}
			{$FIELDS_TYPES.$type_id}
		</td>
		<td>{if $field_def->in_listings}Yes{else}-{/if}</td>
		<td>{if $field_def->in_reports}Yes{else}-{/if}</td>
		<td>
			{if $field_def->in_listings}
				{$field_def->display_width}
			{else}
				- 
			{/if}
		</td>
		<td align="right">
			<a href="/?cl=kawacs&op=peripheral_field_delete&id={$field_def->id}">Delete&nbsp;&#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="7">[No fields defined yet]</td>
	</tr>
	{/foreach}

</table>

</form>

{literal}
<script language="JavaScript">

check_div ('peripheral_class[use_warranty]', 'div_warranty_fields');
check_div ('peripheral_class[use_sn]', 'div_sn_field');
check_div ('peripheral_class[use_web_access]', 'div_web_access_field');
check_div ('peripheral_class[use_net_access]', 'div_net_access_field');
check_div ('peripheral_class[use_snmp]', 'div_snmp');
</script>
{/literal}