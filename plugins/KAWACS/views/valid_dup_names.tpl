{assign var="paging_titles" value="KAWACS, Valid Duplicate Names"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}

<h1>Valid Duplicate Names</h1>

<p class="error">{$error_msg}</p>

<p>The are computers names which are allowed to be used by more than one computer.</p>

<table class="list" width="80%">
	<thead>
	<tr>
		<td width="140">Name</td>
		<td>Computers</td>
		<td width="100"> </td>
	</tr>
	</thead>
	
	{foreach from=$valid_dup_names key=name item=valid_dups}
		<tr>
			<td rowspan="{$valid_dups|@count}">{$name|escape}</td>
			{foreach from=$valid_dups item=valid_dup}
				<td>
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$valid_dup->computer_id}"
					>#{$valid_dup->computer_id}: {$valid_dup->computer->netbios_name|escape}</a>,
					{assign var="customer_id" value=$valid_dup->computer->customer_id}
					Customer: {$customers_list.$customer_id} ({$customer_id})<br/>
				</td>
				<td align="right" nowrap="nowrap">
					<a href="/?cl=kawacs&amp;op=valid_dup_name_delete&amp;id={$valid_dup->id}"
					onclick="return confirm('Are you really sure you want to remove this?');"
					>Remove &#0187;</a>
				</td>
				</tr>
			{/foreach}
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No valid duplicate names defined]</td>
	</tr>
	{/foreach}
</table>