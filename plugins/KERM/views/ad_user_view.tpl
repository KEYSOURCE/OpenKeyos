{assign var="paging_titles" value="KERM, AD Users & Groups, View AD User"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_users"}
{include file="paging.html"}

<h1>AD User : {$ad_user->sam_account_name}</h1>
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
		<td width="35%">{$computer->netbios_name}</td>
	</tr>
	<tr>
		<td><b>Customer:</b></td>
		<td><a href="/?cl=customer&op=customer_edit&id={$customer->id}">{$customer->name} ({$customer->id})</a></td>
		<td><b>Last updated:</b></td>
		<td>{$item->reported|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
</table>
<p>
{if $returl}<a href="{$returl}">&#0171; Return</a>
{else}<a href="/?cl=kerm&op=manage_ad_users">&#0171; Back to AD Users</a>
{/if}
<p>

	
<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="2">Active Directory Data:</td>
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
	
	<!-- Show the "info" fields -->
	<tr class="head">
		<td colspan="2">Account Info:</td>
	</tr>
	{assign var="nrc" value=$index_nrc_info}
	{assign var="val" value=$item_info->val.$nrc}
	
	{if is_array($val->value)}
		{foreach from=$val->value item=val_field key=val_key}
		{** Don't show the account name again *}
		{if $val_key!= 214} 
		<tr>
			<td width="15%">{$item_info->fld_names.$val_key}:</td>
			<td>
				{$item_info->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}
			</td>
		</tr>
		{/if}
		{/foreach}
	{else}
		<td colspan="2" class="light_text">[Information not available]</td>
	{/if}
	
	<!-- Repeat the operation for the "monitoring" fields -->
	<tr class="head">
		<td colspan="2">Additional AD data:</td>
	</tr>
	{assign var="nrc" value=$index_nrc_monitoring}
	{assign var="val" value=$item_monitoring->val.$nrc}
	
	{if is_array($val->value)}
	
		{foreach from=$val->value item=val_field key=val_key}
		<tr>
			<td width="15%">{$item_monitoring->fld_names.$val_key}:</td>
			<td>
				{$item_monitoring->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}
			</td>
		</tr>
		{/foreach}
	{/if}
	
</table>

<p>
{if $returl}<a href="{$returl}">&#0171; Return</a>
{else}<a href="/?cl=kerm&op=manage_ad_users">&#0171; Back to AD Users</a>
{/if}
</p>