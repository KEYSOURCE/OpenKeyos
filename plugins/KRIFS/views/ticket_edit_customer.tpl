{assign var="ticket_id" value=$ticket->id}
{assign var="paging_titles" value="KRIFS, Ticket, Change Customer"}
{assign var="paging_urls" value="/krifs, /krifs/ticket_edit"}
{include file="paging.html"}

<h1>Change Customer</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="data_frm">
{$form_redir}

<table class="list" width="90%">
	<thead>
	<tr>
		<td width="120">Ticket: </td>
		<td># {$ticket->id} : {$ticket->subject|escape}</td>
	</tr>
	</thead>
	
	<tr>
		<td>Customer:</td>
		<td>
			<select name="customer_id">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$ticket->customer_id}
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save" class="button" 
onclick="return confirm('Are you really sure you want to change the customer?');" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>