{assign var="paging_titles" value="KAWACS, Manage MRemote connections"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>Manage MRemote Connections</h1>

<p class="error">{$error_msg}</p>
<form action="" method="POST" name="frm"> 
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['frm'].elements['do_filter_hidden'].value=1; document.forms['frm'].submit();"
			>
				<option value="-1">[Select customer]</option>
				<option value="0" {if $filter.customer_id==0}selected="selected"{/if}>[All]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
	</tr>
</table>
<p />

<table class="list" width="98%">
	<thead>
		<tr>
			<td>ID</td>
			<td>Name</td>
			<td>Computer</td>
			<td>Connection type</td>
			<td>Host</td>
			<td>Protocol</td>
			<td>Port</td>
			<td>Username</td>
			<td>Password</td>
			<td><input type="submit" name="generate" value="Generate">
			{if $xml_config!=''}
			    <a href="{$xml_config}">Download</a>
			{/if}
			</td>
		</tr>
	</thead>
	{foreach from=$nodes item="r_con"}
	{assign var="ci" value=$r_con->connInfo}
	{assign var="cont" value=$r_con->type}
	<tr {if $cont==1}style="color: red;"{/if}>
		<td>{$r_con->id}</td>
		<td>{$r_con->name}</td>
		<td>{$r_con->computer_id}</td>
		<td>{$MREMOTE_CONNECTION_TYPES.$cont}</td>
		{if $cont==0}
			<td>{$ci->hostname}</td>
			<td>{assign var="prot" value=$ci->protocol}{$MREMOTE_PROTOCOLS.$prot}</td>
			<td>{$ci->port}</td>
			<td>{$ci->username}</td>
			<td>{$ci->password}</td>
			<td>&nbsp;</td>
		{else}
			<td colspan="6"></td>
		{/if}
	</tr>
	{/foreach}
</table>
{*
{if $xml_config!=''}
<textarea cols="150" rows="50">
{$xml_config}
</textarea>
{/if}
*}
</form>