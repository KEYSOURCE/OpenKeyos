{assign var="paging_titles" value="KAWACS, MIBs Management"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}


<h1>MIBs Management</h1>

<p class="error">{$error_msg}</p>

<a href="/?cl=snmp&amp;op=mib_add">Upload new MIB &#0187;</a>

<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="30">ID</td>
		<td>Name</td>
		<td>Comments</td>
		<td width="80"> </td>
		<td> </td>
	</tr>
	</thead>
	
	{foreach from=$mibs item=mib}
	<tr>
		<td><a href="/?cl=snmp&amp;op=mib_edit&amp;id={$mib->id}">{$mib->id}</a></td>
		<td><a href="/?cl=snmp&amp;op=mib_edit&amp;id={$mib->id}">{$mib->name|escape}</a></td>
		<td>{$mib->comments|escape|truncate:100:"[...]"|nl2br}</td>
		
		<td><a href="/?cl=snmp&amp;op=mib_download&amp;id={$mib->id}">Download</a></td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=snmp&amp;op=mib_delete&amp;id={$mib->id}"
			onclick="return confirm('Are you really sure you want to delete the MIB {$mib->name|escape}?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No MIBs uploaded yet]</td>
	</tr>
	{/foreach}
</table>