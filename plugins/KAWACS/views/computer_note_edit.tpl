{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Edit Computer Note"}
{assign var="computer_id" value=$computer->id}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="computer_view_link" value="kawacs"|get_link:"computer_view":$p:"template"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:$computer_view_link}
{include file="paging.html"}

<h1>Edit Computer Note</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="15%">Computer:</td>
		<td class="post_highlight">#{$computer->id}: {$computer->netbios_name|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Created by:</td>
		<td class="post_highlight">{$user->get_name()|escape}</td>
	</tr>
	<tr>
		<td class="highlight">Created:</td>
		<td class="post_highlight">{$note->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
	</tr>
	<tr>
		<td class="highlight">Note:</td>
		<td class="post_highlight">
			<textarea name="note[note]" cols="80" rows="12">{$note->note|escape}</textarea>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
