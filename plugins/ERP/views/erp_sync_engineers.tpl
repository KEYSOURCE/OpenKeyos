{assign var="paging_titles" value="Krifs, ERP Synchronization: Engineers"}
{assign var="paging_urls" value="/?cl=krifs"}
{include file="paging.html"}

<h1>ERP Synchronization: Engineers</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}
<table width="98%">
	<tr>
		<td width="60%">
			<input type="submit" name="do_synchronize" value="Syncronize"
				onclick="return confirm('Are you sure you want to proceed with the synchronization?');"
			>
			<p/>
			<b>Note:</b>
			Synchronization will only synchronize travel and service codes. It will <b>NOT</b> create
			new user records.
		</td>
		<td width="40%">
			<ul style="margin-top: 0px; margin-bottom: 0px;">
				<li><font style="color:orange;">Orange</font>: The engineer is defined only in ERP.</li>
				<li><font style="color:blue;">Blue</font>: The engineer is defined only in Keyos.</li>
				<li><font style="color:green;">Green</font>: The travel and service codes don't match between Keyos and ERP.</li>
			</ul>
		</td>
	</tr>
</table>
</form>

<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="5%">KS&nbsp;ID</td>
		<td width="15%">ERP ID</td>
		<td width="20%">User (KS name)</td>
		<td width="30%">User (ERP name)</td>
		<td width="15%" nowrap="nowrap">ERP ID - Service</td>
		<td width="15%" nowrap="nowrap">ERP ID - Travel</td>
	</tr>
	</thead>
	
	{foreach from=$erp_engineers item=eng}
	<tr
		{if $eng->sync_stat==$smarty.const.ERP_SYNC_STAT_KS_NEW} style="color: blue;"
		{elseif $eng->sync_stat==$smarty.const.ERP_SYNC_STAT_ERP_NEW} style="color: orange;"
		{elseif $eng->sync_stat==$smarty.const.ERP_SYNC_STAT_MODIFIED} style="color: green;"
		{/if}
	>
		<td>{$eng->user_id}</td>
		<td>{$eng->erp_id}</td>
		<td>
			{if $eng->user->id}
				{$eng->user->get_name()}
			{/if}
		</td>
		<td>{$eng->erp_name}</td>
		<td>{$eng->erp_id_service}</td>
		<td>{$eng->erp_id_travel}</td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text">[No records]</td>
	</tr>
	{/foreach}

</table>