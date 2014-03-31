{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Monitor Profile, Edit Events Log Reporting"}
{assign var="profile_id" value=$profile->id}
{assign var="p" value="id:"|cat:$profile_id}
{assign var="profile_edit_link" value="kawacs"|get_link:"profile_edit":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles, "|cat:$profile_edit_link}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_no_report = {$smarty.const.EVENTLOG_NO_REPORT};
var cat_ids = new Array ();
cnt = 0;
{foreach from=$EVENTS_CATS key=cat_id item=cat_name}
cat_ids[cnt++] = {$cat_id};
{/foreach}
var types_ids = new Array ();
cnt = 0;
{foreach from=$EVENTLOG_TYPES key=type_id item=type_name}
types_ids[cnt++] = {$type_id};
{/foreach}

{literal}

// Checks what
function checkTypes ()
{
	for (i=0; i<cat_ids.length; i++)
	{
		checkTypesCategory(cat_ids[i]);
		//alert (i);
	}
}

function checkTypesCategory (cat_id)
{
	frm = document.forms['frm_t'];
	elm = frm.elements['default_report['+cat_id+'][]'];
	
	no_report = false;
	for (j=0; j<elm.length; j++)
	{
		if (elm[j].value == type_no_report) no_report = elm[j].checked;
	}
	
	if (no_report)
	{
		// No reporting was selected, deselect and disable all other checkboxes
		for (j=0; j<elm.length; j++)
		{
			if (elm[j].value != type_no_report)
			{
				elm[j].checked = false;
				elm[j].disabled = true;
			}
		}
	}
	else
	{
		for (j=0; j<elm.length; j++) elm[j].disabled = false;
	}
}

{/literal}
//]]>
</script>

<h1>Profile Events Log Reporting</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<p/>

<h2>Default Events to Report</h2>
<p>Specify below the default types of events for each category of events logs.</p>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="120">Category</td>
		<td class="post_highlight">Events types</td>
	</tr>
	</thead>
	
	{foreach from=$profile->default_events_types_requested key=cat_id item=types}
	<tr>
		<td class="highlight" nowrap="nowrap">{$EVENTS_CATS.$cat_id}</td>
		<td class="post_highlight">
			{assign var="cols" value="6"}
			<table class="no_borders">
			{foreach from=$EVENTLOG_TYPES key=type_id item=type_name name=types}
				{if ($smarty.foreach.types.iteration % $cols) == 1}<tr>{/if}
				
				<td nowrap="nowrap" style="padding-right: 10px;">
					<input type="checkbox" name="default_report[{$cat_id}][]" value="{$type_id}"
					{if ($types & $type_id)==$type_id} checked {/if} onclick="checkTypesCategory ({$cat_id});" />
					{$type_name|escape}
				</td>
				
				{if ($smarty.foreach.types.iteration % $cols) == 0}</tr>{/if}
			{/foreach}
			</table>
			{if ($smarty.foreach.types.iteration % $cols) != 0}</tr>{/if}
		</td>
	</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>


<script language="JavaScript" type="text/javascript">
//<![CDATA[
checkTypes ();
//]]>
</script>

<h2>Additional Events Sources to Report</h2>
<p>Specify below additional events sources and types to request from computers, other than the ones covered by the default event types.</p>

{assign var="p" value="id:"|cat:$profile->id}
[ <a href="{'kawacs'|get_link:'monitor_profile_events_src_add':$p:'template'}">Add Events Source &#0187;</a> ]
<p/>

<table width="80%" class="list">
	<thead>
	<tr>
		<td width="120">Category</td>
		<td>Source</td>
		<td>Events types</td>
		<td width="140"> </td>
	</tr>
	</thead>
	
	{foreach from=$profile->events_types_requested item=src}
	<tr>
		<td>
			{assign var="cat_id" value=$src->category_id}
			{$EVENTS_CATS.$cat_id|escape}
		</td>
		<td>
			{assign var="source_id" value=$src->source_id}
			{$sources.$cat_id.$source_id|escape}
		</td>
		<td nowrap="nowrap">{$src->get_events_types_str()|escape}</td>
		<td nowrap="nowrap" align="right">
            {assign var="p" value="id:"|cat:$profile->id|cat:",src_id:"|cat:$src->id}
			<a href="{'kawacs'|get_link:'monitor_profile_events_src_edit':$p:'template'}">Edit</a> |
			<a href="{'kawacs'|get_link:'monitor_profile_events_src_delete':$p:'template'}"
			onclick="return confirm('Are you sure you want to delete this?');"
			>Delete</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">No additional sources defined</td>
	</tr>
	{/foreach}
</table>

<p/>
