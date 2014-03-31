{assign var="paging_titles" value="Krifs, ERP Synchronization: Action Types Categories"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>ERP Synchronization: Action Types Categories</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}
<table width="98%">
	<tr>
		<td width="40%">
			<input type="submit" name="do_synchronize" value="Syncronize"
				onclick="return confirm('Are you sure you want to proceed with the synchronization?');"
			>
			<p/>
			<b>Note:</b> The synchronization will automatically create in Keyos all the new categories from ERP
			and will update the existing ones if they have been modified.
		</td>
		<td width="60%">
			<ul style="margin-top: 0px; margin-bottom: 0px;">
				<li><font style="color:orange;">Orange</font>: The category is defined only in ERP.</li>
				<li><font style="color:red;">Red</font>: The category is defined only in ERP, but the information is incomplete.</li>
				<li><font style="color:green;">Green</font>: The category details differ between ERP and Keyos.</li>
			</ul>
		</td>
	</tr>
</table>
</form>
<p/>
<table class="list" width="60%">
	<thead>
	<tr>
		<td width="10%">KS ID</td>
		<td width="10%">ERP ID</td>
		<td width="40%">KS Name</td>
		<td width="40%">ERP Name</td>
	</tr>
	</thead>

	{foreach from=$erp_actypes_categories item=category}
	<tr
		{if $category->sync_stat==$smarty.const.ERP_SYNC_STAT_KS_NEW} style="color: blue;"
		{elseif $category->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_NEW} style="color: orange;"
		{elseif $category->sync_stat==$smarty.const.ERP_SYNC_STAT_MODIFIED} style="color: green;"
		{elseif $category->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_INCOMPLETE} style="color: red;"
		{/if}
	>
		<td>
			{if $category->id}{$category->id}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $category->erp_id}{$category->erp_id|escape}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $category->category->name}{$category->category->name|escape}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $category->name}{$category->name|escape}
			{else}--
			{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="3">[No records]</td>
	</tr>
	{/foreach}

</table>
<p/>