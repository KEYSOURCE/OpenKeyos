{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Monitor Profile, Edit Monitor Profile Alerts"}
{assign var="profile_id" value=$profile->id}
{assign var="p" value="id:"|cat:$profile_id}
{assign var="profile_edit_link" value="kawacs"|get_link:"profile_edit":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles, "|cat:$profile_edit_link}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}

function addAlert ()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_alerts[]'];
	available_list = frm.elements['available_alerts'];
	sel_item = available_list.selectedIndex;
	
	if (sel_item >= 0)
	{
		new_opt = new Option ();
		new_opt.value = available_list.options[sel_item].value;
		new_opt.text = available_list.options[sel_item].text;
		
		assigned_list.options[assigned_list.options.length] = new_opt;
		available_list.options[sel_item] = null;
	}
	
}

function removeAlert ()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_alerts[]'];
	available_list = frm.elements['available_alerts'];
	sel_item = assigned_list.selectedIndex;
	
	if (sel_item >= 0)
	{
		new_opt = new Option ();
		new_opt.value = assigned_list.options[sel_item].value;
		new_opt.text = assigned_list.options[sel_item].text;
		
		available_list.options[available_list.options.length] = new_opt;
		assigned_list.options[sel_item] = null;
	}
	
}

function selectAllAssignedAlerts ()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_alerts[]'];
	
	for (i=0; i<assigned_list.options.length; i++)
	{
		assigned_list.options[i].selected = true;
	}
	
	return true;
}

{/literal}
//]]>
</script>

<h1>Edit Monitor Profile Alerts</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t" onSubmit="return selectAllAssignedAlerts();">
{$form_redir}

<p/>
<table width="98%" class="list">
	<thead>
	<tr>
		<td width="50%">Assigned alerts</td>
		<td width="50%">Available alerts</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="assigned_alerts[]" size="25" multiple style="width: 350px;" ondblclick="removeAlert()">
				{foreach from=$profile->alerts item=alert}
					{assign var="level" value=$alert->level}
					<option value="{$alert->id}">[{$ALERT_NAMES.$level}] {$alert->name|escape}</option>
				{/foreach}
			</select>
		</td>
		
		<td>
			<select name="available_alerts" size="25" style="width: 350px;" ondblclick="addAlert()">
				{foreach from=$alerts item=alert}
				{if !in_array($alert->id,$assigned_alerts_ids)}
					{assign var="level" value=$alert->level}
					<option value="{$alert->id}">[{$ALERT_NAMES.$level}] {$alert->name|escape}</option> 
				{/if}
				{/foreach}
				
				
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
