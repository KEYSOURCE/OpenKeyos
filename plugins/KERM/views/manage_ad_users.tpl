{assign var="paging_titles" value="KERM, AD Users & Groups"}
{assign var="paging_urls" value="/?cl=kerm"}
{include file="paging.html"}


<h1>AD Users</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter"> 
{$form_redir}

Customer:
<select name="filter[customer_id]" onChange="document.forms['filter'].submit()">
	<option value="">[Select one]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
<p>
</form>

<p>
<h2>AD Users</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td>Account name</td>
		<td>E-mail</td>
		<td>Full name</td>
		<td align="right">Total size</td>
		<td align="right">Profile</td>
		<td align="right">Home</td>
		<td align="right">Mailbox</td>
		<td align="right">Kawacs</td>
	</tr>
	</thead>
	
	{foreach from=$ad_users item=user}
	<tr>
		<td><a href="/?cl=kerm&op=ad_user_view&computer_id={$user->computer_id}&nrc={$user->nrc}">{$user->sam_account_name}</a></td>
		<td>
			{if $user->email}{$user->email|escape}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{$user->display_name|escape}
		</td>
		<td align="right" nowrap="nowrap">
			{if $user->total_size}{$user->total_size|get_memory_string}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
			{if $user->profile_size}{$user->profile_size|get_memory_string}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
			{if $user->home_size}{$user->home_size|get_memory_string}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		
		
		
		<td align="right" nowrap="nowrap">
			{if $user->exchange_mailbox_size}{$user->exchange_mailbox_size|get_memory_string}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right">
			<a href="/?cl=kawacs&op=computer_view&id={$user->computer_id}">#&nbsp;{$user->computer_id}</a>
			
		</td> 
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3">[No AD Users]</td>
	</tr>
	{/foreach}
</table>
<p>

<h2>AD Groups</h2>
<table class="list" width="98%">
	<thead>
	<tr>
		<td>Name</td>
		<td width="45%">Members</td>
		<td width="45%">Member of</td>
	</tr>
	</thead>
	
	{foreach from=$ad_groups item=group}
	<tr>
		<td><a href="/?cl=kerm&op=ad_group_view&computer_id={$group->computer_id}&nrc={$group->nrc}">{$group->name}</a></td>
		<td>
			{$group->member|replace:" , ":"<br>"}
		</td>
		<td>
			{$group->member_of|replace:" , ":"<br>"}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3">[No AD Groups]</td>
	</tr>
	{/foreach}
</table>
<p>