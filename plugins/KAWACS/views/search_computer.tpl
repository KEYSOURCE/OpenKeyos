{assign var="paging_titles" value="KAWACS, Search Computer"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}

<h1>Search Computer</h1>

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
		<td>Computer</td>
		<td>Customer</td>
	</tr>
	</thead>
	
	{foreach from=$computers item=computer}
	<tr>
		<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}">{$computer->id}</a></td>
		<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}">{$computer->netbios_name|escape}</a></td>
		<td>
			{assign var="customer_id" value=$computer->customer_id}
			<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">#{$customer_id}: {$customers_list.$customer_id}</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No computers found]</td>
	</tr>
	{/foreach}
</table>
<p/>
