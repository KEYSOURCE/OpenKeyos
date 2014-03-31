{assign var="paging_titles" value="KERM, Manage customer added AD users"}
{assign var="paging_urls" value="/?cl=kerm"}
{include file="paging.html"}

<h1>Manage AD users creation requests</h1>
<p class="error">{$error_msg}</p>

<form name="frm_usrs" method="POST" action="">
{$form_redir}
<p />
<table width="98%">
	<tr>
		<td width="100px">Customer:</td>
		<td>
			<select name="filter[customers]" onchange="document.forms['frm_usrs'].submit()">
				<option value=-1 {if !$filter.customers}selected{/if}>[All]</option>]
				{html_options options=$customers selected=$filter.customers} 
			</select>
		</td>
		<td width="100px">Status:</td>
		<td align="left">
			<select name="filter[status]" onchange="document.forms['frm_usrs'].submit();">
				<option value=-1 {if $filter.status==-1}selected{/if}>[All]</option>
				{html_options options=$USERS_STATUSES selected=$filter.status}
			</select>
		</td>
		<td align="right">
			{if $tot_users > $filter.limit}
				{if $filter.start > 0} 
					<a href="/?cl=kerm&op=customer_added_users_submit" 
						onClick="document.forms['frm_usrs'].elements['go'].value='prev'; document.forms['frm_usrs'].submit(); return false;"
					>&#0171; Précédent</a>
				{else}
					<font class="light_text">&#0171; Précédent</font>
				{/if}
				<select name="filter[start]" onChange="document.forms['frm_usrs'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_users}
					<a href="/?cl=kerm&op=customer_added_users_submit" 
						onClick="document.forms['frm_usrs'].elements['go'].value='next'; document.forms['frm_usrs'].submit(); return false;" 
					>Suivant &#0187;</a>
				{else}
					<font class="light_text">Suivant &#0187;</font>
				{/if}
			{/if}
		</td>
		<td>
			<input type="submit" name="approve" value="Approve selected users">
		</td>
	</tr>
</table>
<input type="hidden" name="go" value="">
<input type="hidden" name="filter[limit]" value="{$filter.limit}">
<p>

<a href="/?cl=kerm&op=add_ad_user">Add new user &#0187;</a>
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
				<a href="{$sort_url}&order_by=LastName&order_dir={if $filter.order_by=='LastName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Last name
				</a>
				{if $filter.order_by=='LastName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=UserName&order_dir={if $filter.order_by=='UserName' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Login
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
				Group
				</a>
				{if $filter.order_by=='GroupName'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text">
				<a href="{$sort_url}&order_by=customer_id&order_dir={if $filter.order_by=='customer_id' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Customer
				</a>
				{if $filter.order_by=='customer_id'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td class="sort_text"><a href="{$sort_url}&order_by=status&order_dir={if $filter.order_by=='status' and $filter.order_dir=='ASC'}DESC{else}ASC{/if}">
				Status</a>
				{if $filter.order_by=='status'}<img src="/images/{if $filter.order_dir=='ASC'}up{else}down{/if}.gif" width="11" height="6">{/if}
				</td>
				<td>&nbsp;</td>
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
			<td>
			{assign var="cust" value=$user->customer_id}
			{$customers.$cust}
			</td>
			<td>{assign var="stat" value=$user->status}{$USERS_STATUSES.$stat}</td>
			<td><a href="/?cl=kerm&op=modify_user&id={$user->id}">Edit &#0187;</a></td>
			<td><input type="checkbox" name="chk_approve[]" value="{$user->id}"></td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="{if $assigned_customers_count>1}9{else}8{/if}">[No users yet]</td> 
			</tr>
		{/foreach}
</table>
</form>
