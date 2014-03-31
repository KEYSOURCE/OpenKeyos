{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert, Add Condition"}
{assign var="alert_id" value=$alert->id}
{assign var="p" value='id:'|cat:$alert->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts, "|cat:"kawacs"|get_link:"alert_edit":$p:"template"}
{include file="paging.html"}

<h1>Add Alert Condition</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Alert condition definition</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" width="15%">Alert: </td>
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
				
			{else}
				Equals: 
				<input type="hidden" name="cond[criteria]" value="{$smarty.const.CRIT_LIST_EQUALS}">
				<select name="cond[list_values][]">
					<option value="">[Select value]</option>
					{html_options options=$list_values selected=$condition->value}
				</select>
				<br/>
				<font class="light_text">[You'll be able to refine the condition after you add/save the new alert]</font>
			{/if}
		
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

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