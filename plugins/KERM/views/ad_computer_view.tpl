{assign var="paging_titles" value="KERM, AD Computers, View AD Computer"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_computers"}
{include file="paging.html"}

<h1>AD Computer : {$ad_computer->cn}</h1>

<p class="error">{$error_msg}</p>

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

<p><a href="/?cl=kerm&op=manage_ad_computers">&#0171; Back to AD Computers</a></p>

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
	
	<tr class="head">
		<td colspan="2">Active Directory data - Monitoring</td>
	</tr>
	
	{assign var="val" value=$item_monitoring->val.$nrc}
	{if is_array($val->value)}
		{foreach from=$val->value item=val_field key=val_key}
		{if $val_key!=234}
		<tr>
			<td width="15%">{$item_monitoring->fld_names.$val_key}:</td>
			<td>
				{$item_monitoring->get_formatted_value($nrc, $val_key)|replace:" , ":"<br>"}
			</td>
		</tr>
		{/if}
		{/foreach}
	{/if}
</table>
	 

<p>
<a href="/?cl=kerm&op=manage_ad_computers">&#0171; Back to AD Computers</a>
<p>