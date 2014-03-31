{assign var="computer_id" value=$computer->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Computer Events Settings, Edit Events Source"}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="computer_events_settings_link" value="kawacs"|get_link:"computer_events_settings":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link|cat:", "|cat:$computer_events_settings_link}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var type_no_report = {$smarty.const.EVENTLOG_NO_REPORT};
var current_cat = {$src->category_id}
var types_ids = new Array ();
cnt = 0;
{foreach from=$EVENTLOG_TYPES key=type_id item=type_name}
types_ids[cnt++] = {$type_id};
{/foreach}

{literal}

function checkTypes ()
{
	frm = document.forms['frm_t'];
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

<h1>Edit Events Source for Computer</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="frm_t">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td>Computer:</td>
		<td class="post_highlight">#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight" width="140">Category:</td>
		<td class="post_highlight">
			{assign var="cat_id" value=$src->category_id}
			{$EVENTS_CATS.$cat_id}
		</td>
	</tr>
	<tr>
		<td class="highlight">Default computer reporting:</td>
		<td class="post_highlight">
			{$computer->get_events_types_str($cat_id)|escape}
		</td>
	</tr>
	<tr>
		<td class="highlight">Source:</td>
		<td class="post_highlight">
			{assign var="source_id" value=$src->source_id}
			{$sources.$cat_id.$source_id|escape}
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
checkTypes ();
//]]>
</script>