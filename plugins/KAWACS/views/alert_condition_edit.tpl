{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert, Edit Condition"}
{assign var="alert_id" value=$alert->id}
{assign var="p" value='id:'|cat:$alert->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts,"|cat:"kawacs"|get_link:"alert_edit":$p:"temaplate"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function selectAllValues ()
{
	frm = document.forms['data_frm']
	vals_list = frm.elements['cond[list_values][]']
	
	for (i=0; i<vals_list.options.length; i++)
	{
		vals_list.options[i].selected = true
	}
}

function addValue ()
{
	frm = document.forms['data_frm']
	vals_list = frm.elements['cond[list_values][]']
	avail_list = frm.elements['available_values']
	
	if (avail_list.selectedIndex >= 0)
	{
		opt = new Option (avail_list.options[avail_list.selectedIndex].text, avail_list.options[avail_list.selectedIndex].value, false, false)
		
		vals_list.options[vals_list.options.length] = opt
		avail_list.options[avail_list.selectedIndex] = null
	}
}

function removeValue ()
{
	frm = document.forms['data_frm']
	vals_list = frm.elements['cond[list_values][]']
	avail_list = frm.elements['available_values']
	
	if (vals_list.selectedIndex >= 0)
	{
		opt = new Option (vals_list.options[vals_list.selectedIndex].text, vals_list.options[vals_list.selectedIndex].value, false, false)
		
		avail_list.options[avail_list.options.length] = opt
		vals_list.options[vals_list.selectedIndex] = null
	}
}
{/literal}
//]]>
</script>


<h1>Edit Alert Condition</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="data_frm" onsubmit="selectAllValues();">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td colspan="2">Alert condition definition</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" width="110">Alert: </td>
		<td class="post_highlight">{$alert->name|escape}</td>
	</tr>
	<tr>
		<td class="highlight">Monitor item: </td>
		<td class="post_highlight">[{$alert->itemdef->id}] {$alert->itemdef->name}</td>
	</tr>
	{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
	<tr>
		<td class="highlight">Monitor item field: </td>
		<td class="post_highlight">{$condition->fielddef->name|escape}</td>
	</tr>
	{/if}
	
	<tr>
		<td class="highlight">Condition: </td>
		<td class="post_highlight">
			{if $condition->fielddef->type == $smarty.const.MONITOR_TYPE_DATE}
				
				<!-- Defining condition for a date field -->
				<select name="cond[criteria]">
					<option value="">[Select]</option>
					{html_options options=$CRIT_NAMES_DATE selected=$condition->criteria}
				</select>
				<p/>
				<input type="text" name="cond[value]" size="5" value="{$condition->value|escape}"> days
			
			{elseif $condition->fielddef->type == $smarty.const.MONITOR_TYPE_STRING or $condition->fielddef->type == $smarty.const.MONITOR_TYPE_TEXT}
			
				<!-- Defining condition for a string field -->
				<select name="cond[criteria]">
					<option value="">[Select]</option>
					{html_options options=$CRIT_NAMES_STRING selected=$condition->criteria}
				</select>
				<p/>
				<input type="text" name="cond[value]" size="40" value="{$condition->value|escape}">
			
			{elseif $condition->fielddef->type == $smarty.const.MONITOR_TYPE_INT or $condition->fielddef->type == $smarty.const.MONITOR_TYPE_FLOAT}
			
				<!-- Defining condition for a numeric field -->
				<select name="cond[criteria]">
					<option value="">[Select]</option>
					{html_options options=$CRIT_NAMES_NUMBER selected=$condition->criteria}
				</select>
				<p/>
				<input type="text" name="cond[value]" size="10" value="{$condition->value|escape}">
			
			{elseif $condition->fielddef->type == $smarty.const.MONITOR_TYPE_MEMORY}
			
				<!-- Defining condition for a memory field -->
				<select name="cond[criteria]">
					<option value="">[Select]</option>
					{html_options options=$CRIT_NAMES_NUMBER selected=$condition->criteria}
				</select>
				<p/>
				
				<input type="text" name="cond[value]" size="10" value="{$condition->value|escape}">
				<select name="cond[value_type]">
					<option value="">[Select]</option>
					{html_options options=$CRIT_MEMORY_MULTIPLIERS_NAMES selected=$condition->value_type}
				</select>
				
			{elseif $condition->fielddef->type == $smarty.const.MONITOR_TYPE_LIST}
				<b>Select below the values you want to search for:</b><br/>
				
				
				<table class="no_borders">
				<tr>
					<td nowrap="nowrap">
						Criteria:<br/>
						<select name="cond[criteria]">
							<option value="">[Select]</option>
							{html_options options=$CRIT_NAMES_LIST selected=$condition->criteria}
						</select>:
					</td>
					<td>
						Selected values:<br/>
						<select name="cond[list_values][]" size="15" multiple onDblClick="removeValue();" style="width:200px;">
							{foreach from=$list_values key=val_id item=val_name}
							{if in_array($val_id, $condition->list_values)}
							<option value="{$val_id}">{$val_name|escape}</option>
							{/if}
							{/foreach}
						</select>
					</td>
					<td>
						Available values:<br/>
						<select name="available_values" size="15" onDblClick="addValue();"  style="width:200px;">
							{foreach from=$list_values key=val_id item=val_name}
							{if !in_array($val_id, $condition->list_values)}
							<option value="{$val_id}">{$val_name|escape}</option>
							{/if}
							{/foreach}
						</select>
					</td>
				</tr>
				</table>
				
			{/if}
		
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

</form>


{if $condition->fielddef->is_snmp and count($condition->fielddef->snmp_oid_vals)>0}
	<p/>
	<h3>Possible SNMP values for this item:</h3>
	<ul>
	{foreach from=$condition->fielddef->snmp_oid_vals key=key item=val}
		<li><b>{$key}</b> : {$val|escape}</li>
	{/foreach}
	</ul>
{/if}