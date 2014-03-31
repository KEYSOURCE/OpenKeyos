{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert, Edit Alert Profiles"}
{assign var="alert_id" value=$alert->id}
{assign var="p" value='id:'|cat:$alert->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts, "|cat:"kawacs"|get_link:"alert_edit":$p:"template"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}

function addProfile ()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_profiles[]'];
	available_list = frm.elements['available_profiles'];
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

function removeProfile ()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_profiles[]'];
	available_list = frm.elements['available_profiles'];
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

function selectAllAssignedProfiles()
{
	frm = document.forms['frm_t'];
	assigned_list = frm.elements['assigned_profiles[]'];
	
	for (i=0; i<assigned_list.options.length; i++)
	{
		assigned_list.options[i].selected = true;
	}
	
	return true;
}

{/literal}
//]]>
</script>

<h1>Edit Alert Profiles</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t" onSubmit="return selectAllAssignedProfiles();">
{$form_redir}

<p/>
<table width="98%" class="list">
	<thead>
	<tr>
		<td width="50%">Assigned profiles</td>
		<td width="50%">Available profiles</td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="assigned_profiles[]" size="25" multiple style="width: 350px;" ondblclick="removeProfile()">
				{foreach from=$alert->profiles_list key=profile_id item=profile_name}
					<option value="{$profile_id}">[{$profile_id}] {$profile_name|escape}</option>
				{/foreach}
			</select>
		</td>
		
		<td>
			<select name="available_profiles" size="25" style="width: 350px;" ondblclick="addProfile()">
				{foreach from=$profiles_list key=profile_id item=profile_name}
				{if !isset($alert->profiles_list[$profile_id])}
					<option value="{$profile_id}">[{$profile_id}] {$profile_name|escape}</option>
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
