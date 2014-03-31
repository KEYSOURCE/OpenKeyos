{assign var="alert_id" value=$alert->id}
{assign var="p" value='id:'|cat:$alert->id}
{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert, Edit Alert Recipients"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts, "|cat:"kawacs"|get_link:"alert_edit":$p:"template"}
{include file="paging.html"}

<h1>Edit Alert Recipients : {$alert->name}</h1>

<p class="error">{$error_msg}</p>

<p>
If you select one or more recipients here, this setting will supercede both the generic assigned recipients
and the customer assigned recipients.
</p>

<form action="" method="post">
{$form_redir}

<table width="20%" class="list">
	<thead>
	<tr>
		<td colspan="2">User</td>
		<td nowrap="nowrap" align="center">Is default</td>
	</tr>
	</thead>
	
	{foreach from=$users_list key=user_id item=user_name}
	<tr>
		<td width="1%">
			<input type="checkbox" name="recipients[]" value="{$user_id}" class="checkbox"
				{if in_array($user_id,$alert->recipients_ids)}checked{/if}
			/>
		</td>
		<td nowrap="nowrap">{$user_name|escape}</td>
		<td style="padding: 0px;" align="center">
			<input type="radio" name="default_recipient" value="{$user_id}"
				{if $alert->recipient_default==$user_id}checked{/if}
			/>
		</td>
	</tr>
	{/foreach}
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
