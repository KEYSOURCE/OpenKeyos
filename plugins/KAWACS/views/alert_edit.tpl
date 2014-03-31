{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
var send_customer = {$smarty.const.ALERT_SEND_CUSTOMER};
var send_keysource = {$smarty.const.ALERT_SEND_KEYSOURCE};

{literal}
function check_send()
{
	frm = document.forms['alert_frm'];
	boxes_send = frm.elements['alert[send_to][]'];
	row = document.getElementById ('msg_row');
	row_recips = document.getElementById ('recips_row');
	send_to_sum = 0;
	
	for (i=0; i<boxes_send.length; i++)
	{
		if (boxes_send[i].checked) send_to_sum = send_to_sum + parseInt(boxes_send[i].value);
	}
	
	
	if ((send_to_sum & send_customer) == send_customer) row.style.display = '';
	else row.style.display = 'none';
	
	if ((send_to_sum & send_keysource) == send_keysource) row_recips.style.display = '';
	else row_recips.style.display = 'none';
}
{/literal}
//]]>
</script>


<h1>Edit Alert</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="alert_frm">
{$form_redir}
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Alert definition</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" width="20%">Item: </td>
		<td class="post_highlight">[{$item->id}] {$item->name}</td>
	</tr>
	<tr>
		<td class="highlight">Alert name:</td>
		<td class="post_highlight"><input type="text" name="alert[name]" size="40" value="{$alert->name|escape}" /></td>
	</tr>
	<tr>
		<td class="highlight">Severity level:</td>
		<td class="post_highlight">
			<select name="alert[level]">
				<option value="">[Select]</option>
				{html_options options=$ALERT_NAMES selected=$alert->level}
			</select>
		</td>
	</tr>
	{if !$item->is_peripheral_item()}
	<tr>
		<td class="highlight">On contact only:</td>
		<td class="post_highlight">
			<input type="checkbox" class="checkbox" name="alert[on_contact_only]" value="1" {if $alert->on_contact_only}checked{/if} />
			Select this to prevent alerts of this type to be generated when the computer
			didn't contact the Kawacs server.
		</td>
	</tr>
	{/if}
	<tr>
		<td class="highlight">Ignore in days:</td>
		<td class="post_highlight">
			{foreach from=$DAY_NAMES key=day_id item=day_name}
				<input type="checkbox" class="checkbox" name="alert[ignore_days][]" value="{$day_id}"
					{if ($alert->ignore_days&$day_id)==$day_id}checked{/if}
				/>{$day_name}&nbsp;&nbsp;
			{/foreach}
		</td>
	</tr>
	
	<!-- For arrays of non-struct items, allow specifying the joining type AND/OR -->
	<!-- if $alert->itemdef->type != $smarty.const.MONITOR_TYPE_STRUCT and count($alert->conditions)>1 -->
	<tr>
		<td class="highlight">
			Join conditions with:
		</td>
		<td class="post_highlight">
			<select name="alert[join_type]">
				{html_options options=$JOIN_CONDITION_NAMES selected=$alert->join_type}
			</select>
			
			{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
				(Only for multiple conditions set on THE SAME field)
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Send to:</td>
		<td class="post_highlight">
			{foreach from=$ALERT_SEND_TO key=send_to_id item=send_to_name}
				<input type="checkbox" class="checkbox" name="alert[send_to][]" value="{$send_to_id}"
					{if ($alert->send_to&$send_to_id)==$send_to_id}checked{/if}
					onClick="check_send ();"
				/>&nbsp;{$send_to_name}&nbsp;&nbsp;
			{/foreach}
		</td>
	</tr>
	<tr id="recips_row" style="display:none;">
		<td class="highlight" nowrap="nowrap">
			Keysource recipients:&nbsp;&nbsp;&nbsp;
            {assign var="p" value='id:'|cat:$alert->id}
			<a href="{'kawacs'|get_link:'alert_edit_recips':$p:'template'}">Edit &#0187;</a>
		</td>
		<td class="post_highlight">
			{if !$alert->recipients_ids}
				<font class="light_text">[Use default recipients]</font>
			{else}
				{foreach from=$alert->recipients_ids item=recipient_id name=cycle_recips}
					{if $alert->recipient_default==$recipient_id}<b>{/if}
					{$users_list.$recipient_id}{if !$smarty.foreach.cycle_recips.last},{/if}
					{if $alert->recipient_default==$recipient_id}</b>{/if}
				{/foreach}
			{/if}
		</td>
	</tr>
	<tr id="msg_row" style="display:none;">
		<td class="highlight">Customer message:</td>
		<td class="post_highlight">
			Subject: <br/>
			<input type="text" name="alert[subject]" value="{$alert->subject|escape}" size="60" /> <br/>
			Message: <br/>
			<textarea name="alert[message]" rows="6" cols="60">{$alert->message|escape}</textarea>
		</td>
	</tr>

</table>
<p/>
<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

<p/>
<table width="98%">
	<tr>
		<td width="60%">
			<h2>Conditions</h2>
			<p/>
			
			{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
				Field:
				<select name="cond[field_id]">
					{foreach from=$alert->itemdef->struct_fields item=field}
						<option value="{$field->id}">{$field->name}</option>
					{/foreach}
				</select>
			{/if}
			
			<input type="submit" name="add_condition" value="Add Condition &#0187;" class="button" />
			<p/>
			
			<table class="list" width="95%">
				<thead>
				<tr>
					{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
					<td>Field</td>
					{/if}
					<td>Condition</td>
					<td>Comparison value</td>
					<td> </td>
				</tr>
				</thead>
				{foreach from=$alert->conditions item=cond}
				<tr>
					{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
						<td>{$cond->fielddef->name|escape}</td>
					{/if}
				
					{assign var="criteria" value=$cond->criteria}
					<td>{$CRIT_NAMES.$criteria}</td>
			
					<td>
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
							{elseif count($cond->fielddef->snmp_oid_vals)>0}
								{assign var="cond_value" value=$cond->value}
								 
								{if isset($cond->fielddef->snmp_oid_vals.$cond_value)}
								({$cond->fielddef->snmp_oid_vals.$cond_value})
								{/if}
							{/if}
						{/if}
					</td>
					
					<td nowrap="nowrap" align="right">
                        {assign var="p" value='id:'|cat:$cond->id}
						<a href="{'kawacs'|get_link:'alert_condition_edit':$p:'template'}">Edit</a> |
						<a href="{'kawacs'|get_link:'alert_condition_delete':$p:'template'}"
							onClick="return confirm('Are you sure you want to delete this condition?');"
						>Delete</a>
					</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="4" class="light_text">[No conditions defined]</td>
				</tr>
				{/foreach}
			</table>
			
			
			<h2>Values to report</h2>
			<p/>
			<table class="list" width="95%">
				<thead>
				<tr>
					<td>Fields to include:</td>
					<td align="right">
                        {assign var="p" value='id:'|cat:$alert->id}
                        <a href="{'kawacs'|get_link:'alert_edit_fields_send':$p:'template'}">Select fields &#0187;</a>
				</tr>
				</thead>
				{if count($alert->send_fields)>0}
					{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
						{foreach from=$alert->itemdef->struct_fields item=field}
							{if in_array($field->id, $alert->send_fields)}
								<tr>
									<td colspan="2">{$field->name}</td>
								</tr>
							{/if}
						{/foreach}
					{else}
						<tr>
							<td colspan="2">{$alert->itemdef->name|escape}</td>
						</tr>
					{/if}
				{else}
					<tr><td colspan="2" class="light_text">[No fields selected]</td></td>
				{/if}
			</table>
			
		</td>
		<td width="40%">
			<h2>Profiles</h2>
			<p/>
			<input type="submit" name="edit_profiles" value="Edit profiles &#0187;" class="button" />
			<p/>
			
			<table class="list" width="98%">
				<thead>
				<tr>
					<td width="3%">ID</td>
					<td width="97%">Profile name</td>
				</tr>
				</thead>
				
				{foreach from=$alert->profiles_list key=profile_id item=profile_name}
				<tr>
					<td width="3%">{$profile_id}</td>
					<td width="97%">{$profile_name|escape}</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="2" class="light_text">[No profiles]</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>
<p/>
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
check_send ();
//]]>
</script>
