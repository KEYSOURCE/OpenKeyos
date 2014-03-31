{assign var="paging_titles" value="KAWACS, Peripheral Classes, Profile Associations"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=manage_profiles"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var items_noselect = new Array ();
var cnt = 0;
{foreach from=$profile_fields_noselect item=field_id}
items_noselect[cnt++] = '{$field_id}';
{/foreach}

{literal}

function itemChanged (field_id)
{
	var ret = true;
	var frm = document.forms['frm_t'];
	var elm_list = frm.elements['item_ids['+field_id+']'];
	var c_id = elm_list.options[elm_list.selectedIndex].value;
	
	//alert (elm_list.options[elm_list.selectedIndex].value);
	for (var i=0; i<items_noselect.length; i++)
	{
		if (items_noselect[i] == c_id)
		{
			ret = false;
			alert ("You can't select this item, you need to select one of its fields.");
			elm_list.options[0].selected = true;
			break;
		}
	}
	
	return ret;
}

{/literal}
//]]>
</script>


<h1>Peripheral Class - Monitoring Profile Associations</h1>
<p class="error">{$error_msg}</p>

<p>
Below you can specify how the peripheral class fields should be mapped 
to the monitoring items from the selected profile.<br/>
For the fields that you specify associated monitoring items, the
data for these fields will be automatically collected through SNMP.<br/>
However, don't forget that you still need to specify for each peripheral 
what computer will do the SNMP data gathering.
</p>


<form action="" method="post" name="frm_t">
{$form_redir}

<h3>Peripheral class: {$class->name|escape}</h3>
<h3>Monitoring profile: {$profile->name|escape}</h3>
<p/>

<table width="90%" class="list">
	<thead>
	<tr>
		<td>Peripheral class field</td>
		<td class="post_highlight">Associated profile item</td>
	</tr>
	</thead>
	
	{foreach from=$class->field_defs item=field}
	<tr>
		<td width="150" nowrap="nowrap" class="highlight">{$field->name}:</td>
		<td class="post_highlight">
			<select name="item_ids[{$field->id}]" onchange="return itemChanged({$field->id});">
				<option value="" class="light_text">[None]</option>
				{foreach from=$profile_fields key=item_id item=item_name}
					<option value="{$item_id}" 
					{if $class->has_monitor_item ($item_id, $profile->id, $field->id)}selected{/if}
					>{$item_name|escape}</option>
				{/foreach}
				
			</select>
		</td>
		
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No fields defined]</td>
	</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

</form>
