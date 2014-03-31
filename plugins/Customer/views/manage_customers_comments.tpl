{assign var="paging_titles" value="Customers, Manage Customers Comments"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}


<h1>Manage Customers Comments</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="manage_comments">
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			Customer:
			<select name="filter[customer_id]"
				onChange="document.forms['manage_comments'].elements['do_filter_hidden'].value=1; document.forms['manage_comments'].submit();"
			>
				<option value="">[Select]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $filter.customer_id}
				<a href="/?cl=customer&op=customer_comment_add&customer_id={$filter.customer_id}&returl={$returl}">Add comment &#0187;</a>
			{/if}
		</td>
</table>
</form>
<p/> 

{if $filter.customer_id}
<table width="98%" class="list">
	<thead>
	<tr>
		<td width="70%">Subject / Comments</td>
		<td width="20%">Added by</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$comments item=comment}
		<tr>
			<td>
				<a href="/?cl=customer&op=customer_comment_edit&id={$comment->id}&returl={$returl}">{$comment->subject}</a><br/>
				{$comment->comments|escape|nl2br}
			</td>
			<td>
				{$comment->user->get_name()}<br/>
				{$comment->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			</td>
			
			<td align="right" nowrap="nowrap">
				<a href="/?cl=customer&op=customer_comment_delete&id={$comment->id}&returl={$returl}"
					onClick="return confirm ('Are you really sure you want to delete this comment?');"
				>Delete &#0187;</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5">[No comments defined yet]</td>
		</tr>
	{/foreach}

</table>
{/if}