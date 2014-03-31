{assign var="paging_titles" value="KAWACS, Manage Alerts, Add Alert"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_alerts"}
{include file="paging.html"}

<h1>Add Alert</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post">
{$form_redir}

<p/>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="2">Alert definition</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%" class="highlight">Item: </td>
		<td class="post_highlight">[{$item->id}] {$item->name|escape}</td>
	</tr>
	<tr>
		<td class="highlight">Alert name:</td>
		<td class="post_highlight"><input type="text" name="alert[name]" size="40" value="{$alert->name|escape}" /></td>
	</tr>
	<tr>
		<td class="highlight">Severity level:</td>
		<td class="post_highlight">
			<select name="alert[level]">
				<option value="">[Select]</option>
				{html_options options=$ALERT_NAMES selected=$alert->level}
			</select>
		</td>
	</tr>
	
	{if !$item->is_peripheral_item()}
	<tr>
		<td class="highlight">On contact only:</td>
		<td class="post_highlight">
			<input type="checkbox" name="alert[on_contact_only]" value="1" {if $alert->on_contact_only}checked{/if}>
			Select this to prevent alerts of this type to be generated when the computer
			didn't contact the Kawacs server.
		</td>
	</tr>
	{/if}
</table>
<p/>

<input type="submit" name="save" value="Add" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />
</form>
