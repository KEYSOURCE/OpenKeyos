{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, View Item Log "}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}


<h1>View Item Log</h1>

<p class="error">{$error_msg}</p>

<a href="{$computer_view_link}">&#0171; Back to computer</a>

<p>
<form action="" method="POST" name="filter_form">
{$form_redir}

Log interval:
<select name="filter[month]" onChange="document.forms['filter_form'].submit()">
	<option value="">[Last week]</option>
	{html_options values=$months output=$months selected=$filter.month}
</select>
<p>


{if $log_items_count > 0}
<input type="submit" name="clear_log" value="Clear log"
	onClick="return confirm('Are you sure you want to delete all logged values for this item?');"
>
<p>
{/if}

<table width="98%">
	<tr>
		<td width="50%">
			{if $prev_url}
				<a href="{$prev_url}">&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Previous</font>
			{/if}
		</td>
		<td width="50%" align="right">
			{if $log_items_count > $filter.per_page}
				Page:
				<select name="filter[page]" onChange="document.forms['filter_form'].submit()">
					{html_options options=$pages selected=$filter.page}
				</select>
				
				Items per page:
				<select name="filter[per_page]" onChange="document.forms['filter_form'].submit()">
					{html_options options=$per_page_options selected=$filter.per_page}
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			{/if}
			
			{if $next_url}
				<a href="{$next_url}">Next &#0187;</a>
			{else}
				<font class="light_text">Next &#0187;</font>
			{/if}
		</td>
	</tr>
</table>

<table class="list" width="98%">
	<thead>
	<tr>
		<td width="2%">Reported</td>
		{foreach from=$item->fld_names item=name}
			<td>{$name}</td>
		{/foreach}
	</tr>
	</thead>

	{assign var="reported_last" value=""}
	{foreach from=$items_log item=log_item}
		{** It was "from=$item->val", changed to "from=$log_item->val" *}
		{foreach from=$log_item->val item=log_item_val key=log_item_key}
		<tr>
			<td nowrap>
				{if $reported_last != $log_item->reported}
					{$log_item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
					{assign var="reported_last" value=$log_item->reported}
				{/if}
			</td>
			{foreach from=$item->fld_names item=field_name key=field_id}
				<td>
				{$log_item->get_formatted_value($log_item_key, $field_id)}
				</td>
			{/foreach}

		</tr>
		{/foreach}
	{foreachelse}
	<tr>
		<td colspan="2">[There are currently no logged values in the database]</td>
	</tr>
	{/foreach}
</table>
<table width="98%">
	<tr>
		<td width="50%">
			{if $prev_url}
				<a href="{$prev_url}">&#0171; Previous</a>
			{else}
				<font class="light_text">&#0171; Previous</font>
			{/if}
		</td>
		<td width="50%" align="right">
			{if $next_url}
				<a href="{$next_url}">Next &#0187;</a>
			{else}
				<font class="light_text">Next &#0187;</font>
			{/if}
		</td>
	</tr>
</table>
<p>

{if $log_items_count > 0}
<input type="submit" name="clear_log" value="Clear log"
	onClick="return confirm('Are you sure you want to delete all logged values for this item?');"
>
{/if}


</form>