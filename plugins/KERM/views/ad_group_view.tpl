{assign var="paging_titles" value="KERM, AD Users & Groups, View AD Group"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_users"}
{include file="paging.html"}

<h1>AD Group : {$ad_group->cn}</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST">
{$form_redir}

<table width="98%">
	<tr>
		<td width="15%"><b>Kawacs server:</b></td>
		<td width="35%"><a href="/?cl=kawacs&op=computer_view&id={$computer->id}">#&nbsp;{$computer->id}</a></td>
		
		<td width="15%"><b>Netbios name:</b></td>
		<td width="35%">{$computer->netbios_name|escape}</td>
	</tr>
	<tr>
		<td><b>Customer:</b></td>
		<td><a href="/?cl=customer&op=customer_edit&id={$customer->id}">{$customer->name} ({$customer->id})</a></td>
		<td><b>Last updated:</b></td>
		<td>{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
</table>
<p>
<a href="/?cl=kerm&op=manage_ad_users">&#0171; Back to AD Groups</a>
<p>

{if $item->itemdef->type != $smarty.const.MONITOR_TYPE_STRUCT}

	<!-- Not a structure -->
	<table class="list" width="98%">
	<thead><td>{$item->itemdef->name}</td></thead>
	{foreach from=$item->val item=val key=nrc}
		<tr><td>
			{$item->get_formatted_value($nrc)} 
		</td></tr>
	{/foreach}
	</table>
	
{elseif count($item->itemdef->struct_fields) <= 8}

	<!-- Structure, but with not many fields, so fields can be displayed in columns -->
	<table class="list" width="98%">
		<thead>
		<tr>
			{foreach from=$item->itemdef->struct_fields item=field_def}
			<td>
				{$field_def->name}
			</td>
			{/foreach}
		</tr>
		</thead>
		
		{foreach from=$item->val item=val key=nrc}
			<tr>
			<!-- foreach from=$val->value item=val_field key=val_key} -->
			{foreach from=$item->itemdef->struct_fields item=field_def}
				{assign var="val_key" value=$field_def->id}
				<td>{$item->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}</td>
			{/foreach}
			</tr>
		{/foreach}
	</table>
{else}
	
	<!-- Structure with many fields, fields will be displayed in rows -->
	<table class="list" width="98%">
		<thead>
		<tr>
			<td colspan="2">Active Directory data</td>
		</tr>
		</thead>
		
		{assign var="nrc" value=$index_nrc}
		{assign var="val" value=$item->val.$nrc}
		
		{if is_array($val->value)}
		
			{foreach from=$val->value item=val_field key=val_key}
			<tr>
				<td width="15%">{$item->fld_names.$val_key}:</td>
				<td>
					{$item->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}
				</td>
			</tr>
			{/foreach}
		{/if}
		
		{if !$item->itemdef->main_field_id}
			<tr class="head"><td colspan="3"> </td></tr>
		{/if}
		
	</table>
	 

{/if}
<p>
<a href="/?cl=kerm&op=manage_ad_users">&#0171; Back to AD Groups</a>
<p>