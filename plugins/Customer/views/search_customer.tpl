{assign var="paging_titles" value="Customer, Search Customer"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Search Customer</h1>

<p class="error">{$error_msg}</p>

<form action="" method="GET">
{$form_redir}

Search: <input type="text" name="search_text" value="{$search_text|escape}" size="20" />
<input type="submit" name="search" value="Search again &#0187;" class="button" />
</form>

<p/>
<table width="60%" class="list">
	<thead>
	<tr>
		<td width="20">ID</td>
		<td>Customer</td>
		<td width="20" align="center">Active</td>
		<td width="20" align="center">On-hold</td>
	</tr>
	</thead>
	
	{foreach from=$customers item=customer}
	<tr>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">{$customer->id}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">{$customer->name|escape}</a></td>
		<td align="center">
			{if $customer->active}Y{else}N{/if}
		</td>
		<td align="center">
			{if $customer->on_hold}Y{else}-{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No computers found]</td>
	</tr>
	{/foreach}
</table>
<p/>
