{assign var="paging_titles" value="KAWACS, Manage Peripheral Classes, Edit Class, Edit Fields Order"}
{assign var="peripheral_class_id" value=$peripheral_class->id}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_peripheral_classes, /?cl=kawacs&op=peripheral_class_edit&id=$peripheral_class_id"}
{include file="paging.html"}

{literal}
<script language="JavaScript" type="text/javascript">
//<![CDATA[
function do_move (delta)
{
	frm = document.forms ['fields_frm'];
	list = frm.elements ['fields[]'];
	list_sel = list.selectedIndex; 
	
	if (list_sel >= 0)
	{
		if (list_sel+delta >= 0 && list_sel+delta < list.options.length)
		{
			opt = new Option (list.options[list_sel].text, list.options[list_sel].value);
			list.options[list_sel].value = list.options[list_sel+delta].value;
			list.options[list_sel].text = list.options[list_sel+delta].text;
			list.options[list_sel+delta].value = opt.value;
			list.options[list_sel+delta].text = opt.text;
			list.options[list_sel+delta].selected = true;
		}
	}
	else
	{
		alert ('Please select a field first');
	}
	
	return false;
}

function select_all ()
{
	frm = document.forms['fields_frm'];
	list = frm.elements['fields[]'];
	list.multiple = true;
	list.focus ();
		
	for (i=0; i<list.options.length; i++)
	{
		list.options[i].selected = true;
	}
	
	return true;
}

//]]>
</script>
{/literal}

<h1>Edit Peripheral Fields Order</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="fields_frm" onsubmit="return select_all();">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Available fields</td>
	</tr>
	</thead>
	
	<tr>
		<td width="300">
			<select name="fields[]" size="15" style="width:300px">
				{foreach from=$peripheral_class->field_defs item=field}
				<option value="{$field->id}">{$field->name}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<input type="submit" value="Move up" onclick="return do_move (-1);" />
			<input type="submit" value="Move down" onclick="return do_move (1);" />
		</td>
	</tr>
</table>

<p/>
<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Close" />
</form>
