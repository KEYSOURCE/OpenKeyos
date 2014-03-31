{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Edit Class, Edit Field"}
{assign var="peripheral_class_id" value=$peripheral_class->id}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes, /?cl=kawacs&op=peripheral_class_edit&id=$peripheral_class_id"}
{include file="paging.html"}

{literal}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
function check_width ()
{
	frm = document.forms['field_frm']
	elm = frm.elements['peripheral_class_field[in_listings]']
	row = document.getElementById ('row_width')
	
	if (elm.checked)
	{
		row.style.display = '';
	}
	else
	{
		row.style.display = 'none';
	}
}
//]]>
</script>
{/literal}

<h1>Edit Peripheral Field</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="field_frm">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td width="20%">Peripheral class:</td>
		<td>{$peripheral_class->name}</td>
	</tr>
	</thead>
	<tr>
		<td>Field name:</td>
		<td><input type="text" name="peripheral_class_field[name]" size="40" value="{$peripheral_class_field->name}"></td>
	</tr>
	<tr>
		<td>Type:</td>
		<td>
			<select name="peripheral_class_field[type]">
				<option value="">[Select type]</option>
				{html_options options=$FIELDS_TYPES selected=$peripheral_class_field->type}
			</select>
		</td>
	</tr>
	<tr>
		<td>Include in listings:</td>
		<td>
			<input type="checkbox" name="peripheral_class_field[in_listings]" {if $peripheral_class_field->in_listings}checked{/if} onclick="check_width()" />
		</td>
	</tr>
	<tr>
		<td>Include in reports:</td>
		<td>
			<input type="checkbox" name="peripheral_class_field[in_reports]" {if $peripheral_class_field->in_reports}checked{/if} />
		</td>
	</tr>
	<tr id="row_width">
		<td>Relative display width:</td>
		<td>
			<select name="peripheral_class_field[display_width]">
				{html_options output=$peripheral_class_field->width_options values=$peripheral_class_field->width_options selected=$peripheral_class_field->display_width}
			</select>
		</td>
	</tr>
</table>

<p/>
<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>

{literal}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_width ()
//]]>
</script>
{/literal}