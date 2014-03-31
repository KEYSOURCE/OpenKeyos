{assign var="paging_titles" value="Customers, Customers With Suspended Alerts"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}


<h1>Customers With Suspended Alerts</h1>

<p class="error">{$error_msg}</p>

<p>The following customers have e-mail alerts disabled. This means that Keysource 
users will not receive any Kawacs notifications e-mails for those customers. The
notifications are nevertheless created in Kawacs.</p>

<table class="list" width="40%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="99%">Name</td>
	</tr>
	</thead>
	
	{foreach from=$suspended_customers item=customer}
	<tr>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">{$customer->id}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer->id}">{$customer->name}</a></td>
	</tr>
	{foreachelse}
	<tr>
		<td class="light_text" colspan="2">[No customers have the alerts suspended]</td>
	</tr>
	{/foreach}
</table>
