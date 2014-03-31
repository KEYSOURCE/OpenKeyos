{assign var="paging_titles" value="KAWACS, Manage Alerts"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var show_details = {if $filter.details}true{else}false{/if};
var alert_ids = new Array ();
cnt = 0;
{foreach from=$alerts item=alert}
alert_ids[cnt++] = {$alert->id};
{/foreach}

{literal}
// Called when "Create alert" is clicked, to check if an item was selected
function checkSelectedItem ()
{
	ret = true;
	frm = document.forms['frm_t'];
	elm_items = frm.elements['item_id'];
	
	if (elm_items.options[elm_items.selectedIndex].value == '')
	{
		alert ('Please select the monitor item for which you want to define the alert.');
		ret = false;
	}
	
	return ret;
}
{/literal}
//]]>
</script>

<h1>Manage Alerts</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}
<table width="98%" class="list">
	<thead>
	<tr>
		<td>Details</td>
		<td>Items</td>
		<td>Profiles</td>
		<td align="right">Define new alert</td>
	</tr>
	</thead>
	<tr>
		<td>
			<select name="filter[details]" onchange="document.forms['frm_t'].submit();">
				<option value="0">Hide details</option>
				<option value="1" {if $filter.details}selected{/if}>Show details</option>
			</select>
		</td>
		<td>
			<select name="filter[item_id]" onchange="document.forms['frm_t'].submit();">
				<option value="">[Show all items]</option>
				{foreach from=$used_item_ids key=item_id item=item_name}
					<option value="{$item_id}" {if $filter.item_id==$item_id}selected{/if}>[{$item_id}] {$item_name|escape}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<select name="filter[profile_id]" onchange="document.forms['frm_t'].submit();">
				<option value="">[Show all profiles]</option>
				{html_options options=$profiles_list selected=$filter.profile_id}
			</select>
		</td>
		<td align="right">
			<select name="item_id">
				<option value="">[Select item]</option>
				{foreach from=$items_list key=item_id item=item_name}
					<option value="{$item_id}">[{$item_id}] {$item_name|escape}</option>
				{/foreach}
			</select>
			<input type="submit" name="add_alert" value="Create &#0187" class="button" onclick="return checkSelectedItem();"/>
		</td>
	</tr>
</table>
</form>
<p/>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="2%">ID</td>
		<td width="28%">Name</td>
		<td width="5%">Level</td>
		<td width="5%" align="center">Contact only</td>
		<td width="25%">Item</td>
		<td width="25%">Profiles</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$alerts item=alert}
	<tr style="height: 10px;" style="border-bottom:none">
		{assign var="level" value=$alert->level}
		<td>
            {assign var="p" value="id:"|cat:$alert->id}
            <a href="{'kawacs'|get_link:'alert_edit':$p:'template'}">{$alert->id}</a></td>
		<td>
            {assign var="p" value="id:"|cat:$alert->id}
            <a href="{'kawacs'|get_link:'alert_edit':$p:'template'}">{$alert->name|escape}</a>
			{if $filter.details}
					<div style="padding-left:20px; margin-bottom: 10px;">
						{if $alert->conditions}
							{foreach from=$alert->conditions item=cond}
								{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
									{$cond->fielddef->name|escape}:
								{/if}
								
								{assign var="criteria" value=$cond->criteria}
								{$CRIT_NAMES.$criteria}:
								
								{if $cond->fielddef->type == $smarty.const.MONITOR_TYPE_LIST}
									{assign var="list_type" value=$cond->fielddef->list_type}
									{foreach from=$cond->list_values item=list_val name=list_vals}
										{$AVAILABLE_ITEMS_LISTS.$list_type.$list_val}
										{if !$smarty.foreach.list_vals.last}, {/if}
									{/foreach}
								{else}
									{$cond->value|escape}
									{if $cond->fielddef->type == $smarty.const.MONITOR_TYPE_DATE}
										days
									{elseif $cond->value_type}
										{assign var="val_type" value=$cond->value_type}
										{$CRIT_TYPES_NAMES.$val_type}
									{/if}
								{/if}
								<br/>
							{/foreach}
						{else}
							<font class="light_text">[No conditions defined]</font><br/>
						{/if}
						{if $alert->recipients_ids}
							<i>
							{foreach from=$alert->recipients_ids item=recipient_id name=cycle_recips}
								{if $alert->recipient_default==$recipient_id}<b>{/if}
								{$users_list.$recipient_id}{if !$smarty.foreach.cycle_recips.last},{/if}
								{if $alert->recipient_default==$recipient_id}</b>{/if}
							{/foreach}
							</i>
						{/if}
					</div>
			{/if}
		</td>
		<td style="color: {$ALERT_COLORS.$level}; font-weight:800;" nowrap="nowrap">
			{$ALERT_NAMES.$level}
		</td>
		<td align="center">
			{if $alert->on_contact_only}Yes
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			[{$alert->itemdef->id}] {$alert->itemdef->name|escape}
		</td>
		<td>
			{if $alert->profiles_list}
				{if $filter.details}
					{foreach from=$alert->profiles_list key=profile_id item=profile_name}
						#{$profile_id}: {$profile_name|escape}<br/>
					{/foreach}
				{else}
					{$alert->profiles_list|@count} profiles
				{/if}
			{else}
				<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
            {assign var="p" value="id:"|cat:$alert->id}
            <a href="{'kawacs'|get_link:'alert_delete':$p:'template'}" onclick="return confirm('Are you really sure you want to delete this alert definition?');">Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="6">[No alerts defined]</td>
	</tr>
	{/foreach}

</table>

