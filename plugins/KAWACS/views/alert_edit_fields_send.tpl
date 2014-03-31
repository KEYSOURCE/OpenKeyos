{assign var="paging_titles" value="KAWACS, Manage Alerts, Edit Alert, Edit Condition, Values To Report"}
{assign var="alert_id" value=$alert->id}
{assign var="p" value='id:'|cat:$alert->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts, "|cat:"kawacs"|get_link:"alert_edit":$p:"template"}
{include file="paging.html"}

<h1>Values To Report</h1>

<p class="error">{$error_msg}</p>

<p>Select below the values you want to include in the notifications subject when alerts
of this type are being raised.</p>


<form action="" method="post">
{$form_redir}

<table width="80%" class="list">
	<thead>
	<tr>
		<td width="120">Alert:</td>
		<td class="post_highlight">{$alert->name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Fields to include: </td>
		<td class="post_highlight">
			{if $alert->itemdef->type == $smarty.const.MONITOR_TYPE_STRUCT}
				{foreach from=$alert->itemdef->struct_fields item=field}
					<input type="checkbox" name="fields[]" value="{$field->id}" class="checkbox" 
						{if in_array($field->id, $alert->send_fields)} checked {/if}
					/> {$field->name|escape}<br/>
					
				{/foreach}
			{else}
				<input type="checkbox" name="fields[]" value="0 " class="checkbox"
					{if in_array(0, $alert->send_fields)} checked {/if}
				/>
				{$alert->itemdef->name|escape}
			{/if}
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />

</form>