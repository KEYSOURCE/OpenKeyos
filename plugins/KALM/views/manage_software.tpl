{assign var="paging_titles" value="KALM, Manage Software"}
{assign var="paging_urls" value="/?cl=kalm"}
{include file="paging.html"}

{literal}
<script language="JavaScript">

function changePage (start_page)
{
	frm = document.forms['filter_frm']
	
	if (start_page < 0)
	{
		pages = frm.elements['filter_start']
		start_page = pages.options[pages.selectedIndex].value
	}
	
	frm.elements['filter[start]'].value = start_page
	frm.submit ()
}

</script>

{/literal}

<h1>Manage Software Packages</h1>
<p>
<font class="error">{$error_msg}</font>
<form name="filter_frm" action="" method="post">
{$form_redir}
<input type="hidden" name="filter[start]" value="{$filter.start}">

<table class="list" width="98%">
	<thead>
	<tr>
		<td style="width: 60%;">Search</td>	
		<td style="width: 30%;">Per Page</td>
		<td style="width: 10%;">&nbsp;</td>
	</tr>
	</thead>
	<tr>
		<td>
			<div style="float: left;">
			<input type="text" name="filter[search_text]" size="50" value="{$filter.search_text|escape}">
			</div>
		</td>
		<td>
			<select style="float: right;" name="filter[limit]">
				{html_options options=$PER_PAGE_OPTIONS selected=$filter.limit} 
			</select>
		</td>
		<td align="right">
			<input type="submit" value="Apply">
		</td>
	</tr>
</table>
<p>

<table width="98%">
	<tr>
		<td width="50%">			
			<a href="/?cl=kalm&op=software_add">Add new software &#0187;</a>
		</td>
		<td width="50%" align="right">
			{if $sw_count > $filter.limit}
				{if $filter.start > 0}
					<a href="#" onClick="changePage({$start_prev});">&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				
				<select name="filter_start" onChange="changePage (-1)">
					{html_options options=$pages selected=$filter.start}
				</select>
				
				{if $filter.start + $filter.limit < $sw_count}
					<a href="#" onClick="changePage({$start_next});">Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		
		</td>
	</tr>
</table>

<table class="list" width="95%">
	<thead>
		<tr>
			<td>Name</td>
			<td>Manufacturer</td>
			<td>Licensing types</td>
			<td>Report</td>
			<td>Name matching rules</td>
			<td>
				Customers<br/>
				licenses&nbsp;/&nbsp;all
			</td>
			<td> </td>
		</tr>
	</thead>	
	
	{foreach from=$softwares item=software}
	
		<tr>
			<td><a href="/?cl=kalm&op=software_edit&id={$software->id}">{$software->name}</a></td>
			<td>{$software->manufacturer}</td>
			<td>
				{foreach from=$LIC_TYPES_NAMES key=type_id item=type_name}
					{if (($software->license_types&$type_id) == $type_id)}
						{$type_name}<br>
					{/if}
				{/foreach}
			</td>
			<td>{if $software->in_reports}Yes{else}No{/if}</td>
			<td>
				<!-- Shown only for 'Per seat' licenses -->
				
				{if ($software->license_types & $smarty.const.LIC_TYPE_SEAT) == $smarty.const.LIC_TYPE_SEAT}
				
					{foreach from=$software->match_rules item=rule}

						{assign var="match_type" value=$rule->match_type}
						{$NAMES_MATCH_TYPES.$match_type} : 
						{$rule->expression}<br>
					{foreachelse}
						<b>[No rules defined yet ! ]</b>
					{/foreach}
					
				{else}
					(n/a)
				{/if}
			</td>
			<td nowrap="nowrap">
				{if $software->customers or $software->all_customers}
					{$software->customers|@count} / {$software->all_customers|@count} :
					<a href="/?cl=kalm&op=software_customers&id={$software->id}">view &#0187;</a>
				{else}
					<font class="light_text">--</font>
				{/if}
			</td>
			<td>
				<a href="/?cl=kalm&op=software_delete&id={$software->id}"
					onClick="return confirm('Are you sure you want to delete this package?');"
				>Delete</a>
			</td>
		</tr>
	
	{foreachelse}
		<tr>
			<td colspan="3">[No software packages defined yet]</td>
		</tr>
	{/foreach}
</table>
</form>
