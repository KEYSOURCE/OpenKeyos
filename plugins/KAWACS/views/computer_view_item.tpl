{assign var="computer_id" value=$computer->id}
{assign var="item_id" value=$item->itemdef->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, View Item"}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}

<h1>View Computer Item : {$item->itemdef->name}</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter">
<input type="hidden" name="go" value="" />
{$form_redir}

<table width="40%" class="list">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td>#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	</thead>

	<tr>
		<td><b>Item ID:</b></td>
		<td><b>{$item->itemdef->id}</b></td>
	</tr>
	<tr>
		<td><b>Last updated:</b></td>
		<td><b>{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</b></td>
	</tr>
</table>
<p/>

<table width="98%">
	<tr><td width="20%">
		<a href="{$computer_view_link}">&#0171; Back to computer</a>
	</td><td align="right" nowrap="nowrap">
		Per page:
		<select name="filter[limit]" onchange="document.forms['filter'].submit()">
			{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit}
		</select>
		
		{if count($pages)>1}
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			{if $filter.start > 0} 
				<a href="#" 
					onClick="document.forms['filter'].elements['go'].value='prev'; document.forms['filter'].submit(); return false;"
				>&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Previous</font>
			{/if}
			
			<select name="filter[start]" onChange="document.forms['filter'].submit()">
				{html_options options=$pages selected=$filter.start}
			</select>
			
			{if $filter.start + $filter.limit < $tot_items}
				<a href="#" 
					onClick="document.forms['filter'].elements['go'].value='next'; document.forms['filter'].submit(); return false;" 
				>Next &#0187;</a>
			{else}
				<font class="light_text">Next &#0187;</font>
			{/if}
		{/if}
	</td>
</table>
<p/>

{if $item->itemdef->type != $smarty.const.MONITOR_TYPE_STRUCT}

	<!-- Not a structure -->
	<table class="list" width="98%">
	<thead><td>{$item->itemdef->name}</td></thead>
	{foreach from=$item->val item=val key=idx}
		<tr><td>
			{$item->get_formatted_value($idx)} 
		</td></tr>
	{/foreach}
	</table>
	
{elseif count($item->itemdef->struct_fields) <= 8}

	<script language="JavaScript" type="text/javascript">
	//<![CDATA[
	{literal}
		function showTextDiv (suffix)
		{
			elm = document.getElementById ('txt_all_'+suffix);
			elm.style.display = '';
		}
		
		function hideTextDiv (suffix)
		{
			elm = document.getElementById ('txt_all_'+suffix);
			elm.style.display = 'none';
		}
	{/literal}
	//]]>
	</script>

	<!-- Structure, but with not many fields, so fields can be displayed in columns -->
	<table class="list" width="98%">
		<thead>
		<tr>
			{if $item->itemdef->id==$smarty.const.EVENTS_ITEM_ID}
				<td></td>
			{/if}
			{foreach from=$item->itemdef->struct_fields item=field_def}
			<td>
				{$field_def->name}
			</td>
			{/foreach}
			{if $item->itemdef->id==$smarty.const.EVENTS_ITEM_ID}
				<td></td>
			{/if}
		</tr>
		</thead>
		
		{foreach from=$item->val item=val key=idx}
			{assign var="nrc" value=$item->val.$idx->nrc}
			
			<tr>
			{if $item->itemdef->id==$smarty.const.EVENTS_ITEM_ID}
				<td width="16">
					{assign var="event_type_field" value=$smarty.const.FIELD_ID_EVENTS_LOG_TYPE}
					{assign var="event_type" value=$item->val.$idx->value.$event_type_field}
					
					{if $EVENTLOG_TYPES_ICONS.$event_type}
						<img src="/images/icons/{$EVENTLOG_TYPES_ICONS.$event_type}" width="16" height="16" alt="" title=""/>
					{/if}
				</td>
			{/if}
			
			{foreach from=$item->itemdef->struct_fields item=field_def}
				<td>
					{assign var="val_key" value=$field_def->id}
					{if $field_def->type==$smarty.const.MONITOR_TYPE_TEXT}
						{assign var="all_text" value=$item->get_formatted_value($idx, $val_key)}
						{if strlen($all_text) < 50 }
							{$all_text}
						{else}
							<div id="txt_short_{$val_key}_{$nrc}" onmouseover="showTextDiv('{$val_key}_{$nrc}');"
								onmouseout="hideTextDiv('{$val_key}_{$nrc}');">
								{$all_text|truncate:50:''}

                                {assign var="p" value="id:"|cat:$computer_id|cat:",item_id:"|cat:$item_id|cat:",nrc:"|cat:$nrc}
								{assign var="popup_url" value="{'kawacs'|get_link:'computer_view_item_detail':$p:'template'}"}
								<a href="#" onclick="window.open ('{$popup_url}', 'Item detail', 'dependent, scrollbars=yes, resizable=yes, width=600, height=500'); return false;" style="white-space:nowrap;">[...]</a>
							</div>
							
							<div id="txt_all_{$val_key}_{$nrc}" class="info_box" style="display:none; margin-left: -150px;">
								{$all_text}
							</div>
						{/if}
					{else}
						{$item->get_formatted_value($idx, $val_key)|escape}
					{/if}
				</td>
			{/foreach}
			
			{if $item->itemdef->id==$smarty.const.EVENTS_ITEM_ID}
				<td width="60" nowrap="nowrap" align="right">
					{assign var="event_ignored_field" value=$smarty.const.FIELD_ID_EVENTS_LOG_IGNORED}
					{assign var="event_ignored" value=$item->val.$idx->value.$event_ignored_field}
					
					{if $event_ignored}
                        {assign var="p" value="id:"|cat:$computer_id|cat:",nrc:"|cat:$nrc}
						<a href="{'kawacs'|get_link:'computer_event_unignore':$p:'template'}"
						onclick="return confirm('Are you sure you want to un-ignore this?\nALL similar events will be un-ignored.');"
						>Un-ignore</a>
					{else}
                        {assign var="p" value="id:"|cat:$computer_id|cat:",nrc:"|cat:$nrc}
						<a href="{'kawacs'|get_link:'computer_event_ignore':$p:'template'}"
						onclick="return confirm('Are you sure you want to ignore this?\nALL similar events will be marked as ignored');"
						>Ignore</a>
					{/if}
				</td>
			{/if}
			</tr>
		{/foreach}
	</table>
{else}
	
	<script language="JavaScript">
	var groups = new Array ({$item->val|@count})
	var groups_stat = new Array ({$item->val|@count})
	var counter = -1
	var all_collapsed = true
	</script>

	<!-- Structure with many fields, fields will be displayed in rows -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="10"><a href="#" onClick="action_all ();"><img src="/images/expand.gif" width="10" height="11" id="img_all"></a></td>
			<td width="15%">Field</td>
			<td>Value</td>
		</tr>
		</thead>
		
		{foreach from=$item->val item=val key=idx}
			<script language="JavaScript">
				counter++
				i = 0
				groups[counter] = new Array ({$val->value|@count})
				groups_stat[counter] = false;
			</script>

			{assign var="icon_shown" value=0}
			{if $item->itemdef->main_field_id}
				{assign var="main_field_id" value=$item->itemdef->main_field_id}
				{assign var="icon_shown" value=1}
				<tr class="head">
					<td width="10">
						<a href="#" onClick="group_action({$idx});"> <img src="/images/expand.gif" width="10" height="11" id="img_{$idx}"></a>
					</td>
					<td>{$item->fld_names.$main_field_id}:</td>
					<td>{$item->get_formatted_value($idx, $main_field_id)}</td>
				</tr>
			{/if}
		
			{if is_array($val->value)}
			
				{foreach from=$val->value item=val_field key=val_key}
				{if $val_key != $item->itemdef->main_field_id}
				
				<tr {if $icon_shown} id="group_row_{$idx}_{$val_key}" {/if}>
				
					<script language="JavaScript">
					groups[counter][i++] = {$idx} + '_' + {$val_key}
					</script>
					
					{if !$item->itemdef->main_field_id and !$icon_shown}
						{assign var="icon_shown" value=1}
						<td width="10">
							<a href="#" onClick="group_action({$idx});"> <img src="/images/expand.gif" width="10" height="11" id="img_{$idx}"></a>
						</td>
					{else}
						<td> </td>
					{/if}
					
					<td>{$item->fld_names.$val_key}:</td>
					<td>{$item->get_formatted_value($idx, $val_key)}</td>
				</tr>
				{/if}
				{/foreach}
			{/if}
			
			{if !$item->itemdef->main_field_id}
				<tr class="head"><td colspan="3"> </td></tr>
			{/if}
		{/foreach}
	</table>
	 
	{literal}
	<script language="JavaScript">
	
	function action_all ()
	{
		img = document.getElementById('img_all')
		if (all_collapsed)
		{
			img.src = '/images/collapse.gif'
			all_collapsed = false
		}
		else
		{
			img.src = '/images/expand.gif'
			all_collapsed = true
		}
	
		for (gr=0; gr<groups.length; gr++)
		{
			stat = groups_stat[gr]
			if (stat == all_collapsed)
			{
				group_action (gr)
			}
		}
	}
	
	action_all ();
	
	function group_action (id)
	{
		stat = groups_stat[id]
		groups_stat[id] = (!groups_stat[id])
	
		img = document.getElementById('img_'+id)
		if (stat) img.src = '/images/expand.gif'
		else img.src = '/images/collapse.gif'
	
		for (i=0; i<groups[id].length; i++)
		{
			if (line = document.getElementById('group_row_'+groups[id][i]))
			{
				if (stat) line.style.display = 'none'
				else line.style.display = '';
			}
		}
	}
	
	</script>
	
	{/literal}

{/if}
<p>
<a href="{$computer_view_link}">&#0171; Back to computer</a>
<p>
