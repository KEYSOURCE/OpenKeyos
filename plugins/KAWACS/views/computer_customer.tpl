{assign var="computer_id" value=$computer->id}
{assign var="paging_titles" value="KAWACS, Manage Computers, View Computer, Computer Customer"}
{assign var="p" value="id:"|cat:$computer->id}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_computers, "|cat:"kawacs"|get_link:"computer_view":$p:"template"}
{include file="paging.html"}

<h1>Reassign Computer to Customer</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST">
{$form_redir}

<p>Here you can reassign a computer to a different customer.</p>

<table class="list" width="60%">
	<thead>
	<tr>
		<td width="120">Computer:</td>
		<td class="post_highlight">#{$computer->id}: {$computer->netbios_name}</td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Current customer:</td>
		<td class="post_highlight">{$current_customer->name|escape} ({$current_customer->id})</td>
	</tr>
	<tr>
		<td class="highlight">New customer:</td>
		<td class="post_highlight">
			<select name="customer_id">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list}
			</select>
		</td>
	</tr>
</table>
<p/>

{if count($tickets_history)>0}
<b>NOTE:</b> There are a number of tickets linked to this computer.
If you want to reassign some of them to the new customer, you can
do that from the tickets' pages:
<p/>
<ul>
	{foreach from=$tickets_history item=ticket}
	<li>
		{assign var="status" value=$ticket->status}
        {assign var="p" value="id:"|cat:$ticket->id}
		[{$TICKET_STATUSES.$status}]&nbsp;<a href="{'krifs'|get_link:'ticket_edit':$p:'template'}">#{$ticket->id}: {$ticket->subject|escape}</a>
	</li>
	{/foreach}
</ul>

<p/>
{/if}

<input type="submit" name="save" value="Save" class="button" />
<input type="submit" name="cancel" value="Close" class="button" />
</form>
