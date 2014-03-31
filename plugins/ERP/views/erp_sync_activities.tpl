{assign var="paging_titles" value="Krifs, ERP Synchronization: Activities (Timesheets)"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>ERP Synchronization: Activities (Timesheets)</h1>

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
			<b>Note:</b> The synchronization will automatically create in Keyos all the new action types from ERP
			(where all needed information is available) and will update the existing ones if they have been modified
			in ERP.
			<p/>
			If an action type exists in Keyos but doesn't exist anymore in ERP, it will be automatically disabled in 
			Keyos.
		</td>
		<td width="60%">
			<ul style="margin-top: 0px; margin-bottom: 0px;">
				<li><font style="color:orange;">Orange</font>: The article is defined only in ERP.</li>
				<li><font style="color:red;">Red</font>: The article is defined only in ERP, but the information is incomplete.</li>
				<li><font style="color:blue;">Blue</font>: The article is defined only in Keyos.</li>
				<li><font style="color:green;">Green</font>: The article details differ between ERP and Keyos.</li>
			</ul>
		</td>
	</tr>
</table>
</form>
<p/>
<table class="list" width="70%">
	<thead>
	<tr>
		<td width="5%">KS&nbsp;ID</td>
		
		<td width="25%">ERP ID</td>
		<td width="35%">KS&nbsp;Name</td>
		<td width="35%">ERP&nbsp;Name</td>
	</tr>
	</thead>

	{foreach from=$erp_activities item=activity}
	<tr
		{if $activity->sync_stat==$smarty.const.ERP_SYNC_STAT_KS_NEW} style="color: blue;"
		{elseif $activity->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_NEW} style="color: orange;"
		{elseif $activity->sync_stat==$smarty.const.ERP_SYNC_STAT_MODIFIED} style="color: green;"
		{elseif $activity->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_INCOMPLETE} style="color: red;"
		{/if}
	>
		<td>
			{if $activity->activity_id}{$activity->activity_id}
			{else}--
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $activity->erp_id}{$activity->erp_id|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $activity->activity_id}{$activity->activity->name|escape}
			{else}--
			{/if}
		</td>
		<td>
			{if $activity->erp_name}{$activity->erp_name|escape}
			{else}--
			{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="9">[No records]</td>
	</tr>
	{/foreach}

</table>
<p/>
Total action types: {$erp_activities|@count}