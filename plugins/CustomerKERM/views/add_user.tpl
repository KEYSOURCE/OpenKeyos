{assign var="paging_titles" value="Technical Support, Manage users, Add new user"}
{assign var="paging_urls" value="/?cl=customer_kerm&op=manage_users, /?cl=customer_kerm&op=add_new_user"}
{include file="paging.html"}

<h1>Add a new exchange user</h1>
<p class="error">{$error_msg}</p>

<form name="frm_add_usr" method="POST" action="">
{$form_redir}
<table class="list" width="98%">
	<thead>
		<tr>
			<td colspan="2">User Information</td>
		</tr>
	</thead>
	{if $assigned_customers_count > 1}
	<tr>
		<td width="150px" class="headlight">Customer: </td>
		<td class="post_headlight">
			<select id="aduser[customer_id]" name="aduser[customer_id]" onchange="document.forms.frm_add_usr.submit();">
			{html_options options=$customers_list selected=$fc}
			</select>
		</td>
	</tr>
	{else}
	<tr>
		<td width="150px" class="headlight">Customer: </td>
		<td class="post_headlight">
		{$customer}
		<input type="hidden" name="aduser[customer_id]" value="{$k}">
		</td>
	</tr>	
	{/if}
	<tr>
		<td width="150px" class="headlight">
			First name:
		</td>
		<td class="post_highlight"><input type="text" size="40" name="aduser[FirstName]" value="{$aduser.FirstName}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Last name:
		</td>
		<td  class="post_highlight"><input type="text" size="40" name="aduser[LastName]" value="{$aduser.LastName}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Initials:
		</td>
		<td  class="post_highlight"><input type="text" size="40" name="aduser[MiddleInitials]" value="{$aduser.MiddleInitials}">
		</td>
	</tr>
</table>
<table class="list" width="98%">
	<thead>
		<tr>
			<td colspan="2">Active Directory Information</td>
		</tr>
	</thead>
	<tr>
		<td width="150px" class="headlight">
			User login:
		</td>
		<td  class="post_highlight"><input type="text" size="40" name="aduser[UserName]" value="{$aduser.UserName}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Password:
		</td>
		<td  class="post_highlight"><input type="password" size="40" name="aduser[Password]" value="{$aduser.Password}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Email:
		</td>
		<td  class="post_highlight"><input type="text" size="40" name="aduser[Email]" value="{$aduser.Email}">
		<select name="aduser[Domain]">
		{foreach from=$domains item="domain"}
			<option value="@{$domain}">@{$domain}</option>
		{/foreach}
		</select>
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			AD Group:
		</td>
		<td  class="post_highlight">
		{assign var="groups" value=$groups_list.$fc}
		{assign var="grp" value=$aduser.GroupName}
		<select name="aduser[GroupName]" id="aduser[GroupName]">
		{html_options options=$groups selected=$group}
		</select>
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			User active status:
		</td>
		<td  class="post_highlight">		
		<select name="aduser[Active]" id="aduser[Active]">
			<option value="0" {if $aduser.Active == 0}selected="selected"{/if}>Disabled</option>
			<option value="1" {if $aduser.Active == 1}selected="selected"{/if}>Active</option>
		</select>
		</td>
	</tr>
</table>
<table class="list" width="98%">
	<thead>
		<tr>
			<td colspan="2">Additional information</td>
		</tr>
	</thead>
	<tr>
		<td width="150px" class="headlight">
			Postal Address
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[PostalAddress]" value="{$aduser.PostalAddress}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Mailing Address
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[MailingAddress]" value="{$aduser.MailingAddress}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Residential Address
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[ResidentialAddress]" value="{$aduser.ResidentialAddress}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Title
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[Title]" value="{$aduser.Title}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Home Phone
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[HomePhone]" value="{$aduser.HomePhone}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Office Phone
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[OfficePhone]" value="{$aduser.OfficePhone}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Mobile Phone
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[Mobile]" value="{$aduser.Mobile}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Fax
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[Fax]" value="{$aduser.Fax}">
		</td>
	</tr>
	<tr>
		<td width="150px" class="headlight">
			Url
		</td>
		<td class="post_highlight">
			<input type="text" size="40" name="aduser[Url]" value="{$aduser.Url}">
		</td>
	</tr>
</table>
<p>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">
</form>