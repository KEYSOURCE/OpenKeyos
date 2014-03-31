{assign var="paging_titles" value="KAWACS, Valid Duplicate Names, Add Name"}
{assign var="paging_urls" value="/?cl=kawacs, /?cl=kawacs&op=valid_dup_names"}
{include file="paging.html"}

<h1>Add Valid Duplicate Name</h1>

<p class="error">{$error_msg}</p>

<p>Specify below the computers which are allowed to use the name.</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">Name:</td>
		<td class="post_highlight">
			<input type="text" name="dup_name" value="{$dup_name|escape}" size="40" />
		</td>
	</tr>
	</thead>
	<tr>
		<td class="highlight">Computers:</td>
		<td class="post_highlight">
			{foreach from=$computers item=computer}
				<input type="checkbox" name="computers_ids[]" value="{$computer->id}" class="checkbox" 
				{if in_array($computer->id, $selected_ids)}checked{/if}
				/>
				<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}">#{$computer->id}: {$computer->netbios_name|escape}</a>,
				{assign var="customer_id" value=$computer->customer_id}
				Customer: {$customers_list.$customer_id} ({$customer_id})
				<p/>
				
			{foreachelse}
				<font class="light_text">[No computers with this name were found in the database]</font>
			{/foreach}
		</td>
	</tr>

</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>