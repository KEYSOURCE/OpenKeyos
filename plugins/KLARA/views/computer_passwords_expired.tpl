{assign var="paging_titles" value="KLARA, Access Information, Expired Passwords"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_access"}
{include file="paging.html"}

{if $computer->id}
	{assign var="computer_id" value=$computer->id}
	<h1>Expired Passwords : {$computers_list.$computer_id}</h1>
{else}
	{assign var="computer_id" value=0}
	<h1>Expired Passwords : [Network passwords]</h1>
{/if}

<p class="error">{$error_msg}</p>

<a href="{$return_url}">&#0171; Return</a>
<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td width="20%">Login</td>
		<td width="20%">Password</td>
		<td width="40%">Comments</td>
		<td width="10%">Expired</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$passwords item=password}
	{if $password->date_removed and $password->computer_id==$computer_id}
		<tr>
			<td>{$password->login}</td>
			<td>{$password->password}</td>
			<td>{$password->comments|escape|nl2br}</td>
			<td>{$password->date_removed|date_format:$smarty.const.DATE_FORMAT_SMARTY}</td>
			<td align="right">
				<a href="/?cl=klara&amp;op=computer_password_delete&amp;id={$password->id}&amp;ret=expired&amp;computer_id={$computer_id}&amp;customer_id={$customer->id}"
					onClick="return confirm('Are you sure you want to delete this password?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{/if}
	{/foreach}
</table>
<p/>
<a href="{$return_url}">&#0171; Return</a>