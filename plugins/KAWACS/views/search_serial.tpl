{assign var="paging_titles" value="KAWACS, Search Serial"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}

<h1>Search Serial Number</h1>

<p class="error">{$error_msg}</p>

<form action="" method="GET">
{$form_redir}

Search: <input type="text" name="search_text" value="{$search_text|escape}" size="20" />
<input type="submit" name="search" value="Search again &#0187;" class="button" />
</form>

<p/>
{if sizeof($data_computers) != 0}
<p>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="4">Computers</td>
	</tr>
	<tr>
		<td>Computer ID</td>
		<td>Computer name</td>
		<td>Customer</td>
		<td>Serial number</td>
	</tr>
	</thead>
	
	{foreach from=$data_computers item=reg}
	<tr>
		<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$reg->id}">{$reg->id}</a></td>
		<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$reg->id}">{$reg->netbios_name}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$reg->cid}">{$reg->name|escape}</a></td>
		<td>{$reg->value|escape}</td>
	</tr>
	{/foreach}
</table>
<p/>
{/if}
{if sizeof($data_peripherals.sn) != 0}
<p>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="4">Peripherals - Serial numbers</td>
	</tr>
	<tr>
		<td>Peripheral ID</td>
		<td>Peripheral name</td>
		<td>Customer</td>
		<td>Serial number</td>
	</tr>
	</thead>
	
	{foreach from=$data_peripherals.sn item=reg}
	<tr>
		<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$reg->id}">{$reg->id}</a></td>
		<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$reg->id}">{$reg->pname}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$reg->cid}">{$reg->name|escape}</a></td>
		<td>{$reg->value|escape}</td>
	</tr>
	{/foreach}
</table>
<p/>
{/if}
{if sizeof($data_peripherals.pn) != 0}
<p>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="4">Peripherals - product numbers</td>
	</tr>
	<tr>
		<td>Peripheral ID</td>
		<td>Peripheral name</td>
		<td>Customer</td>
		<td>Product number</td>
	</tr>
	</thead>
	
	{foreach from=$data_peripherals.pn item=reg}
	<tr>
		<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$reg->id}">{$reg->id}</a></td>
		<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$reg->id}">{$reg->pname}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$reg->cid}">{$reg->name|escape}</a></td>
		<td>{$reg->value|escape}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No serials found]</td>
	</tr>
	{/foreach}
</table>
<p/>
{/if}
{if sizeof($data_adprinters) != 0}
<p>
<table width="80%" class="list">
	<thead>
	<tr>
		<td colspan="4">AD Printers</td>
	</tr>
	<tr>
		<td>ADPrinter ID</td>
		<td>ADPrinter name</td>
		<td>Customer</td>
		<td>Serial number</td>
	</tr>
	</thead>
	
	{foreach from=$data_adprinters item=reg}
	<tr>
		<td><a href="/?cl=kerm&amp;op=ad_printer_view&amp;computer_id={$reg->cid}&amp;nrc={$reg->nrc}">{$reg->cid}_{$reg->nrc}</a></td>
		<td><a href="/?cl=kerm&amp;op=ad_printer_view&amp;computer_id={$reg->cid}&amp;nrc={$reg->nrc}">{$reg->cn}</a></td>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$reg->id}">{$reg->name|escape}</a></td>
		<td>{$reg->sn|escape}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[No serials found]</td>
	</tr>
	{/foreach}
</table>
<p/>
{/if}