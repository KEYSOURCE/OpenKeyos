{assign var="paging_titles" value="KAWACS, Manage Peripherals Classes"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA

var classes_ids = new Array ();
var cnt = 0;
{foreach from=$peripherals_classes item=class}
classes_ids[cnt++] = {$class->id};
{/foreach}

{literal}
function move_class (direction)
{
	frm = document.forms['filter']
	elm = frm.elements['positions[]']
	
	idx = elm.selectedIndex
	if (idx >= 0)
	{
		new_pos = idx + direction
		if (new_pos>=0 && new_pos<elm.options.length-1)
		{
			txt = elm.options[idx].text
			val = elm.options[idx].value
			elm.options[idx].text = elm.options[new_pos].text
			elm.options[idx].value = elm.options[new_pos].value
			elm.options[new_pos].text = txt
			elm.options[new_pos].value = val
			elm.options[new_pos].selected = true
		}
	}
	else
	{
		alert ('Please select a peripheral class from the list first');
	}
}

function prepare_submit ()
{
	frm = document.forms['filter']
	element = frm.elements['positions[]']
	element.multiple = true;
	element.focus ();
	
	for (i=0; i<element.options.length; i++)
	{
		element.options[i].selected = true;
	}
	
	return true;
}

function hide_fields ()
{
	var elm;
	elm = document.getElementById ('link_hide');
	elm.style.display = 'none';
	elm = document.getElementById ('link_show');
	elm.style.display = '';
	
	for (var i=0; i<classes_ids.length; i++)
	{
		elm = document.getElementById ('fields_' + classes_ids[i]);
		elm.style.display = 'none';
		elm = document.getElementById ('fields_summary_' + classes_ids[i]);
		elm.style.display = '';
	}
	return false;
}

function show_fields ()
{
	var elm;
	elm = document.getElementById ('link_hide');
	elm.style.display = '';
	elm = document.getElementById ('link_show');
	elm.style.display = 'none';
	
	for (var i=0; i<classes_ids.length; i++)
	{
		elm = document.getElementById ('fields_' + classes_ids[i]);
		elm.style.display = '';
		elm = document.getElementById ('fields_summary_' + classes_ids[i]);
		elm.style.display = 'none';
	}
	return false;
}

{/literal}

//]]>
</script>

<h1>Manage Peripherals Classes</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter" onSubmit="return prepare_submit ();"> 
{$form_redir}

<p>
<a href="#" onclick="return hide_fields();" id="link_hide">[ Hide fields ]</a>
<a href="#" onclick="return show_fields();" id="link_show">[ Show fields ]</a>
&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
<a href="/?cl=kawacs&op=peripheral_class_add">Add peripheral class &#0187;</a>
</p>

<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10">ID</td>
			<td width="25%">Name</td>
			<td width="30%">Fields</td>
			<td align="center" width="40">SNMP</td>
			<td colspan="2" width="20%" nowrap>Customers and peripherals</td>
			<td></td>
		</tr>
		</thead>
		
		{foreach from=$peripherals_classes item=peripheral_class}
		{assign var="class_id" value=$peripheral_class->id}
		<tr>
			<td width="10">
                {assign var="p" value="id:"|cat:$peripheral_class->id}
                <a href="{'kawacs'|get_link:'peripheral_class_edit':$p:'template'}">{$peripheral_class->id}</a></td>
			<td><a href="{'kawacs'|get_link:'peripheral_class_edit':$p:'template'}">{$peripheral_class->name}</a></td>
			
			<td>
				<div id="fields_{$peripheral_class->id}">
				{foreach from=$peripheral_class->field_defs item=field_def}
					{$field_def->name}<br>
				{foreachelse}
					[No fields]
				{/foreach}
				</div>
				
				<div id="fields_summary_{$peripheral_class->id}" style="display:none;">
				{if count($peripheral_class->field_defs) > 0}[{$peripheral_class->field_defs|@count} fields]
				{else}[No fields]
				{/if}
				</div>
			</td>
			<td align="center">
				{if $peripheral_class->use_snmp}Yes{else}-{/if}
			</td>
			<td nowrap>
				{if $customer_peripherals_count.$class_id}
					{$customer_peripherals_count.$class_id} customers,<br> 
					{$peripherals_count.$class_id} peripherals
				{else}
					[None]
				{/if}
			</td>
			<td nowrap>
				{if $customer_peripherals_count.$class_id}
                    {assign var="p" value="id:"|cat:$peripheral_class->id}
					<a href="{'kawacs'|get_link:'peripheral_class_customers':$p:'template'}">Customers&nbsp;&#0187;</a><br/>
					<a href="{'kawacs'|get_link:'peripheral_class_peripherals':$p:'template'}">Peripherals&nbsp;&#0187;</a>
				{/if}
			</td>
			
			<td align="right">
                {assign var="p" value="id:"|cat:$peripheral_class->id}
				<a href="{'kawacs'|get_link:'peripheral_class_delete':$p:'template'}">Delete&nbsp;&#0187</a>
			</td>
		</tr>	
		{foreachelse}
		<tr>
			<td colspan="6">[No peripheral classes defined]</td>
		</tr>
		{/foreach}
</table>
<p>

<h2>Display Order</h2>
<p>
<table>
	<tr>
		<td>
			<select name="positions[]" size="8" style="width: 200px"> 
				{foreach from=$peripherals_classes item=peripheral_class}
					<option value="{$peripheral_class->id}">{$peripheral_class->name}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<a href="#" onClick="move_class(-1); return false">Move up</a> |
			<a href="#" onClick="move_class(1); return false">Move down</a>
			<br><br><br>
			<input type="submit" name="reorder" value="Save order">
		</td>
	</tr>
</table>

</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
hide_fields ();
//]]
</script>