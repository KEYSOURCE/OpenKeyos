{assign var="paging_titles" value="Technical Support, Manage Users"}
{assign var="paging_urls" value="/?cl=customer_kerm&op=manage_users"}
{include file="paging.html"}

<h1>Manage exchange user accounts</h1>
<p class="error">{$error_msg}</p>
<form name="frm_manage_users" method="POST" action="">
{$form_redir}
<p />
<table width="98%">
	<tr>
		<td width="100px">Status:</td>
		<td align="left">
			<select name="filter[status]" onchange="document.forms['frm_manage_users'].submit();">
				<option value=-1 {if $filter.status==-1}selected{/if}>[All]</option>
				{html_options options=$USERS_STATUSES selected=$filter.status}
			</select>
		</td>
	</tr>
</table>
<p />
<table width="98%">
	<tr>
		<td colspan="2" width="50%">
			<a href="/?cl=customer_kerm&op=add_user">Add user &#0187;</a>
		</td>
		<td align="right">
			{if $tot_users > $filter.limit}
				{if $filter.start > 0} 
					<a href="/?cl=customer_kerm&op=manage_users_submit" 
						onClick="document.forms['frm_manage_users'].elements['go'].value='prev'; document.forms['frm_manage_users'].submit(); return false;"
					>&#0171; Précédent</a>
				{else}
					<font class="light_text">&#0171; Précédent</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['frm_manage_users'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_users}
					<a href="/?cl=customer_kerm&op=manage_users_submit" 
						onClick="document.forms['frm_manage_users'].elements['go'].value='next'; document.forms['frm_manage_users'].submit(); return false;" 
					>Suivant &#0187;</a>
				{else}
					<font class="light_text">Suivant &#0187;</font>
				{/if}
			{/if}
		</td>
	</tr>
</table>

<input type="hidden" name="go" value="">
<input type="hidden" name="filter[limit]" value="{$filter.limit}">
	<table class="list" width="98%">
		<thead>	
			<tr>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=FirstName&order_dir={if $filter.order_by=='FirstName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				First name
				</a>
				{if $filter.order_by=='FirstName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=LAstName&order_dir={if $filter.order_by=='LastName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Last name
				</a>
				{if $filter.order_by=='LastName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=UserName&order_dir={if $filter.order_by=='UserName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				User login
				</a>
				{if $filter.order_by=='UserName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=Email&order_dir={if $filter.order_by=='Email' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Email
				</a>
				{if $filter.order_by=='Email'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=GroupName&order_dir={if $filter.order_by=='GroupName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				AD Group
				</a>
				{if $filter.order_by=='GroupName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				{if $assigned_customers_count>1}<td>Customer</td>{/if}
				<td class="sort_text"><a href="{$sort_url}&order_by=status&order_dir={if $filter.order_by=='status' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Status</a>
				{if $filter.order_by=='status'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td>&nbsp;</td>
				
			</tr>
		</thead>
		{foreach from=$users item="user"}
		<tr>
			<td>{$user->FirstName|escape}</td>
			<td>{$user->LastName|escape}</td>
			<td>{$user->UserName|escape}</td>
			<td>{$user->Email|escape}</td>
			<td>{$user->GroupName|escape}</td>
			{* {if $assigned_customers_count>1} *}
				<td>
				{assign var="cid" value=$user->customer_id}
				{$customers_list.$cid}
				</td>
				{* {/if} *}
			<td>{assign var="stat" value=$user->status}{$USERS_STATUSES.$stat}</td>
			<td><a href="/?cl=customer_kerm&op=modify_user&id={$user->id}">Modify &#0187;</a></td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="{if $assigned_customers_count>1}8{else}7{/if}">[No users yet]</td> 
			</tr>
		{/foreach}
	</table>
</form>
