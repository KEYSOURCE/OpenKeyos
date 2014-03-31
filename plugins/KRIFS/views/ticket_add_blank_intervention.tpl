{assign var="paging_titles" value="KRIFS, Create Blank Intervention Report"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}


<h1>Create Blank Intervention Report</h1>

<p class="error">{$error_msg}</p>

<form name="frm" method="post">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td colspan="3">Intervention Report</td>
	</tr>
	</thead>
	
	<tr>
		<td width="15%">Customer:</td>
		<td colspan="2">#{$customer->id}: {$customer->name}</td>
	</tr>
	<tr>
		<td>Ticket:</td>
		<td colspan="2">#{$ticket->id}: {$ticket->subject}</td>
	</tr>
	<tr>
		<td>Subject:</td>
		<td colspan="2"><input type="text" name="intervention[subject]" value="{$intervention->subject|escape}" size="60"/></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Create" />
<input type="submit" name="cancel" value="Cancel" />

</form>