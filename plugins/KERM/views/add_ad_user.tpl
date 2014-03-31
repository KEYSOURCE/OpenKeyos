{assign var="paging_titles" value="KERM, Manage customer added users, Add new user"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=customer_added_users"}
{include file="paging.html"}

<h1>Add new AD user</h1>
<p class="error">{$error_msg}</p>

<form name="frm_add" method="POST" action="">
{$form_redir}
<table class="list" width="98%">
	<thead>
		<tr>
			<td colspan=2>Add new AD user</td>
		</tr>
	</thead>
	<tr>
		<td width="150px" class="headlight">Customer: </td>
		<td class="post_headlight">
			<select id="aduser[customer_id]" name="aduser[customer_id]" onchange="document.forms.frm_add.submit();">
			<option value="0">[Select a customer]</option>
			{html_options options=$customers selected=$aduser.customer_id}
			</select>
		</td>
	</tr>
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
	{*<tr>
		<td width="150px" class="headlight">
			User login:
		</td>
		<td  class="post_highlight"><input type="text" size="40" name="aduser[UserName]" value="{$aduser.UserName}">
		</td>
	</tr>*}
	<tr>
		<td width="150px" class="headlight">
			Email:
		</td>
		<td  class="post_highlight">
		{assign var="eml" value='[@]'|@split:$aduser.Email}
		<input type="text" size="40" name="aduser[Email]" value="{$eml[0]}">
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
		{assign var="grp" value=KermADUser::get_group($aduser.GroupName)}
		<select name="aduser[GroupName]" id="aduser[GroupName]">
		{html_options options=$groups_list selected=$grp}
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
	<tr>
		<td width="150px" class="headlight">
			Status
		</td>
		<td class="post_highlight">
			<select name="aduser[status]">
			{html_options options=$USERS_STATUSES selected=$aduser.status}
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
			<input type="text" size="40" name="aduser[PostalAddress]" value="{$aduser.ostalAddress}">
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
<p />
<input type="submit" name='save' value="Save">
<input type="submit" name="cancel" value="Cancel">
</form>