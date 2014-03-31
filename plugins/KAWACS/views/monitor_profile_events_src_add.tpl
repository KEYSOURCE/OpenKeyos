{assign var="paging_titles" value="KAWACS, Manage Profiles, Edit Monitor Profile, Edit Monitor Profile Alerts, Add Events Source"}
{assign var="profile_id" value=$profile->id}
{assign var="p" value="id:"|cat:$profile_id}
{assign var="profile_edit_link" value="kawacs"|get_link:"profile_edit":$p:"template"}
{assign var="monitor_profile_events_edit_link" value="kawacs"|get_link:"monitor_profile_events_edit":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_profiles, "|cat:$profile_edit_link|cat:", "|cat:$monitor_profile_events_edit_link}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_no_report = {$smarty.const.EVENTLOG_NO_REPORT};
var cat_ids = new Array ();
cat_ids[0] = 0;
cnt = 1;
{foreach from=$EVENTS_CATS key=cat_id item=cat_name}
cat_ids[cnt++] = {$cat_id};
{/foreach}
var types_ids = new Array ();
cnt = 0;
{foreach from=$EVENTLOG_TYPES key=type_id item=type_name}
types_ids[cnt++] = {$type_id};
{/foreach}

{literal}

function categoryChanged ()
{
	frm = document.forms['frm_t'];
	lst_cats = frm.elements['src[category_id]'];
	current_cat = lst_cats.options[lst_cats.selectedIndex].value;
	
	for (i=0; i<cat_ids.length; i++)
	{
		div_defaults = document.getElementById ('def_cat_'+cat_ids[i]);
		div_sources = document.getElementById ('src_cat_'+cat_ids[i]);
		div_defaults.style.display = 'none';
		div_sources.style.display = 'none';
	}
	
	div_defaults = document.getElementById ('def_cat_'+current_cat);
	div_sources = document.getElementById ('src_cat_'+current_cat);
	div_defaults.style.display = '';
	div_sources.style.display = '';
}

function checkTypes ()
{
	frm = document.forms['frm_t'];
	lst_cats = frm.elements['src[category_id]'];
	current_cat = lst_cats.options[lst_cats.selectedIndex].value;
	elm = frm.elements['src[types][]'];
	
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

<h1>Add Events Source</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td>Profile:</td>
		<td class="post_highlight">{$profile->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" width="140">Category:</td>
		<td class="post_highlight">
			<select name="src[category_id]" onchange="categoryChanged();">
				<option value="0">[Select category]</option>
				{html_options options=$EVENTS_CATS selected=$src->category_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Default profile reporting:</td>
		<td class="post_highlight">
			<div id="def_cat_0" class="light_text">[Select a category first]</div>
			{foreach from=$EVENTS_CATS key=cat_id item=cat_name}
				<div id="def_cat_{$cat_id}" style="display:none;">{$profile->get_events_types_str($cat_id)|escape}</div>
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="highlight">Source:</td>
		<td class="post_highlight">
			<div id="src_cat_0" class="light_text">[Select a category first]</div>
			{foreach from=$EVENTS_CATS key=cat_id item=cat_name}
				<div id="src_cat_{$cat_id}" style="display:none;">
					<select name="sources[{$cat_id}]">
						<option value="0">[Select source]</option>
						{html_options options=$sources.$cat_id selected=$src->source_id}
					</select>
				</div>
			{/foreach}
			
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Event types:</td>
		
		<td class="post_highlight">
			{assign var="cols" value="3"}
			<table class="no_borders">
			{foreach from=$EVENTLOG_TYPES key=type_id item=type_name name=types}
				{if ($smarty.foreach.types.iteration % $cols) == 1}<tr>{/if}
				
				<td nowrap="nowrap" style="padding-right: 10px;">
					<input type="checkbox" name="src[types][]" value="{$type_id}"
					{if ($src->types & $type_id)==$type_id} checked {/if} onclick="checkTypes ();" />
					{$type_name|escape}
				</td>
				
				{if ($smarty.foreach.types.iteration % $cols) == 0}</tr>{/if}
			{/foreach}
			</table>
			{if ($smarty.foreach.types.iteration % $cols) != 0}</tr>{/if}
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
categoryChanged ();
checkTypes ();
//]]>
</script>