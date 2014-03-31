{assign var="paging_titles" value="KAWACS, RBL Status"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<h1>RBL Statuses</h1>
<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}

<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
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
			<td>Customer</td>
			<td>IP</td>			
			<td>Status</td>
			<td>Details</td>
			<td>Computers</td>
		</tr>
	</thead>
	{foreach from=$stats item="stat" key="cid"}
		{foreach from=$stat item="ip"}
			{assign var="lst" value=0}
			{if $ip.listed == "Listed"}
				{assign var="lst" value=1}
			{/if}
			<tr>
				<td>
                    {assign var="p" value="id:"|cat:$cid}
                    <a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list.$cid}</a></td>
				<td>{$ip.ip}</td>				
				<td><font color="{$ip.color}">{$ip.listed}</font></td>
				<td>
					{if $lst==1}
						{assign var="details" value=$ip.details}
						{assign var="txt" value=$details.txt}
						<b>Listed on: </b>{$details.dnsbl}<br />
						<b>Record: </b>{$details.record}<br />
						<b>Reference: </b>{$txt.0}
					{else}
						&nbsp;
					{/if}
				</td>
				<td>
					{assign var="server" value=$ip.server}
					{foreach from=$server.computers item="comp_name" key="comp_id"}
                        {assign var="p" value="id:"|cat:$comp_id}
                        <a href="{'kawacs'|get_link:'computer_view':$p:'template'}">(#{$comp_id}) {$comp_name}</a><br />
					{/foreach}
				</td>
			</tr>
		{/foreach}
	{/foreach}
</table>
<p/>