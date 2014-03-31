{assign var="paging_titles" value="KERM, Logon Computers"}
{assign var="paging_urls" value="/?cl=kerm"}
{include file="paging.html"}

<h1>Logon Computers{if $customer->id}: {$customer_name|escape}{/if}</h1>

<p class="error_msg">{$error_msg}</p>

<form action="" method="POST" name="filter">
{$form_redir}

Customer:
<select name="filter[customer_id]" onChange="document.forms['filter'].submit()">
	<option value="">[Select one]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
&nbsp;&nbsp;&nbsp;
In the last:
<select name="filter[months]" onChange="document.forms['filter'].submit()">
	<option value="1" {if $filter.months==1}selected{/if}>1 month</option>
	<option value="2" {if $filter.months==2}selected{/if}>2 months</option>
	<option value="3" {if $filter.months==3}selected{/if}>3 months</option>
	<option value="4" {if $filter.months==4}selected{/if}>4 months</option>
</select>
&nbsp;&nbsp;&nbsp;
View by:
<select name="filter[view_by]" onChange="document.forms['filter'].submit()">
	<option value="user">Users</option>
	<option value="computer" {if $filter.view_by!="user"}selected{/if}>Computers</option>
</select>

</form>
<p/>

{if intval($filter.customer_id)}
{if $filter.view_by == 'user'}
<p>Below you have the users from Active Directory and the computers on which they have been
recorded as logged on in the selected interval.<p/>
<table class="list" width="95%">
	<thead>
	<tr>
		<td width="10%">Login</td>
		<td width="25%">Display name</td>
		<td width="25%">E-mail</td>
		<td width="25%">Computers</td>
		<td width="15%" align="right">Last recorded access</td>
	</tr>
	</thead>
	
	{foreach from=$ad_users key=idx item=ad_user}
	{assign var="user_cnt" value=$used_computers_count.$idx}
	{if $user_cnt>1}{assign var="rowspan" value=" rowspan=\"$user_cnt\""}
	{else}{assign var="rowspan" value=""}
	{/if}
	<tr>
		<td{$rowspan} nowrap="nowrap">
			<a href="/?cl=kerm&amp;op=ad_user_view&amp;computer_id={$ad_user->computer_id}&amp;nrc={$ad_user->nrc}&amp;returl={$ret_url}">{$ad_user->sam_account_name|escape}</a>
		</td>
		<td{$rowspan}>
			<a href="/?cl=kerm&amp;op=ad_user_view&amp;computer_id={$ad_user->computer_id}&amp;nrc={$ad_user->nrc}&amp;returl={$ret_url}">{$ad_user->display_name|escape}</a>
		</td>
		<td{$rowspan}>{$ad_user->email|escape}</td>
		
		{if count($used_computers.$idx) == 0}
			<td class="light_text" colspan="2">[No computers found]</td>
			</tr>
		{else}
			{foreach from=$used_computers.$idx key=computer_id item=timestamp name=computers}
				{if !$smarty.foreach.computers.first}<tr>{/if}
				
				<td>
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">#{$computer_id}: {$computers_list.$computer_id|escape}</a>
				</td>
				<td nowrap="nowrap" align="right">{$timestamp|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
				
				</tr>
			{/foreach}
		{/if}
	
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No AD users found]</td>
	</tr>
	{/foreach}
</table>
{else}
<p>Below you have the customer's computers and the Active Directory users which were recorded as being logged on those
computers in the selected interval.</p>
<table class="list" width="95%">
	<thead>
	<tr>
		<td width="25%">Computer</td>
		<td width="10%">Login</td>
		<td width="25%">Display name</td>
		<td width="25%">E-mail</td>
		<td width="15%" align="right">Last recorded access</td>
	</tr>
	</thead>

	{foreach from=$computers_users key=computer_id item=users_idx}
	{assign var="users_cnt" value=$computers_users_count.$computer_id}
	{if $users_cnt>1}{assign var="rowspan" value=" rowspan=\"$users_cnt\""}
	{else}{assign var="rowspan" value=""}
	{/if}
	<tr>
		<td{$rowspan}><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">#{$computer_id}: {$computers_list.$computer_id|escape}</a></td>
		{if $users_cnt==0}
			<td colspan="4" class="light_text">[No users recorded]</td>
		{else}
			{foreach from=$users_idx key=idx item=timestamp name=users}
				{if !$smarty.foreach.users.first}<tr>{/if}
				{assign var="ad_user" value=$ad_users.$idx}
				<td><a href="/?cl=kerm&amp;op=ad_user_view&amp;computer_id={$ad_user->computer_id}&amp;nrc={$ad_user->nrc}&amp;returl={$ret_url}">{$ad_user->sam_account_name}</a></td>
				<td><a href="/?cl=kerm&amp;op=ad_user_view&amp;computer_id={$ad_user->computer_id}&amp;nrc={$ad_user->nrc}&amp;returl={$ret_url}">{$ad_user->display_name}</a></td>
				<td>{$ad_user->email}</td>
				<td nowrap="nowrap" align="right">{$timestamp|date_format:$smarty.const.DATE_TIME_FORMAT_LONG_SMARTY}</td>
				
				</tr>
			{/foreach}
		{/if}
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No computers]</td>
	</tr>
	{/foreach}
</table>

{/if}
{/if}
<p/>
