{assign var="paging_titles" value="KAWACS, Internet Monitoring"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
function addNewIP()
{
	frm = document.forms['frm_t'];
	elm_customers = frm.elements['filter[customer_id]'];
	customer_id = elm_customers.options[elm_customers.selectedIndex].value;
	
	if (customer_id == '') alert ('Please select a customer first.');
	else
	{
		document.location = '/kawacs/monitored_ip_add?customer_id='+customer_id;
	}
	
	return false;
}
{/literal}
//]]>
</script>

<h1>Internet Monitoring</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_t">
{$form_redir}

<table class="list" width="98%">
	<thead>
	<tr>
		<td>Customer</td>
		<td> </td>
	</tr>
	</thead>
	
	<tr>
		<td>
			<select name="filter[customer_id]" onchange="document.forms['frm_t'].submit()">
				<option value="">[All customers]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
		</td>
		
		<td align="right" nowrap="nowrap">
			<a href="#" onclick="return addNewIP();">Add new IP &#0187;</a>
		</td>
	</tr>
</table>
</form>
<p/>

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td width="9%">Monitored IP</td>
		<td width="10%">Target IP</td>
		<td width="5%">Enabled</td>
		<td width="25%">Customer / Internet contract</td>
		<td width="5%">Status</td>
		<td width="5%">Ping&nbsp;OK</td>
		<td width="13%">Last ping</td>
		<td width="12%">Last traceroute</td>
		<td width="5%">Duration</td>
		<td width="10%"> </td>
	</tr>
	</thead>
	
	{foreach from=$monitored_ips item=monitored_ip}
	<tr 
	{if $monitored_ip->disabled}class="light_text"
	{elseif $monitored_ip->is_down()}class="error"
	{/if}
	>
		<td>
            {assign var="p" value="id:"|cat:$monitored_ip->id}
            <a href="{'kawacs'|get_link:'monitored_ip_edit':$p:'template'}">{$monitored_ip->id}</a></td>
		<td nowrap="nowrap">
            <a href="{'kawacs'|get_link:'monitored_ip_edit':$p:'template'}">{$monitored_ip->remote_ip}</a></td>
		<td nowrap="nowrap">
            <a href="{'kawacs'|get_link:'monitored_ip_edit':$p:'template'}">{$monitored_ip->target_ip}</a></td>
		
		<td>
			{if $monitored_ip->disabled}No
			{else}Yes
			{/if}
		</td>
		<td>
            {assign var="p" value="id:"|cat:$monitored_ip->customer_id|cat:",returl:"|cat:$ret_url}
			<a href="{'customer'|get_link:'customer_edit':$p:'template'}">
			#{$monitored_ip->customer->id}: {$monitored_ip->customer->name|escape}</a>
		</td>
		<td>
			{assign var="status" value=$monitored_ip->status}
			{$MONITOR_STATS.$status}
		</td>
		<td {if !$monitored_ip->is_down() and !$monitored_ip->ping_ok}class="warning"{/if}>
			{if $monitored_ip->ping_ok}Yes
			{else}<b>NO</b>
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $monitored_ip->last_ping_test}{$monitored_ip->last_ping_test|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td nowrap="nowrap">
			{if $monitored_ip->last_traceroute_test}{$monitored_ip->last_traceroute_test|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td>
			{if $monitored_ip->last_test_duration}{$monitored_ip->last_test_duration}s
			{else}<font class="light_text">--</font>
			{/if}
		</td>
		<td align="right" nowrap="nowrap">
            {assign var="p" value="id:"|cat:$monitored_ip->id}
            <a href="{'kawacs'|get_link:'monitored_ip_delete':$p:'template'}" onclick="return confirm('Are you really sure you want to delete this?');"
			>Delete &#0187;</a>
		</td>
	</tr>
	{if $monitored_ip->comments or $monitored_ip->internet_contract_id}
	<tr>
		<td> </td>
		<td colspan="3">{$monitored_ip->comments|escape|nl2br}</td>
		<td colspan="3">
			{if $monitored_ip->internet_contract}
                {assign var="p" value="id:"|cat:$monitored_ip->internet_contract_id|cat:",returl:"|cat:$ret_url}
				<a href="{'klara'|get_link:'customer_internet_contract_edit':$p:'template'}"
				>{$monitored_ip->internet_contract->provider->name|escape}: {$monitored_ip->internet_contract->provider_contract->name|escape}</a>
			{/if}
		</td>
		<td colspan="3"> </td>
	</tr>
	{/if}
	{foreachelse}
	<tr>
		<td colspan="10" class="light_text">[No monitored IPs]</td>
	</tr>
	{/foreach}
</table>
<p/>

<b>NOTES:</b>
<ol>
<li><b>Monitored IP</b> is a remote IP through which a computer is connecting to Keyos. <b>Target IP</b> is an IP address which should be reachable by 
traceroute in order to consider the associated <b>Monitored IP</b> as being OK. They can be the same, but they can also be different, e.g.: if the 
<b>Remote IP</b> is not reachable by traceroute, then you will use as <b>Target IP</b> the IP of the last router reachable from Internet before the
<b>Remote IP</b>.<p/></li>

<li>If a monitored IP is marked as <b>disabled</b>, this means that the IP will not be checked during the verification cycles.<p/></li>

<li>The <b>Ping OK</b> flag only signals if the <b>Target IP</b> responds to ping requests. Even if it doesn't respond 
to ping, a connection is considered as OK if the <b>Target IP</b> is reached by traceroute. This can happend if, for
example, the IP is behind a firewall which explicetly blocks ping requests.<p/></li>

<li>Even though it is not manadatory for <b>Target IP</b> to respond to ping, it would be better if they do, in order
to reduce the number of traceroute requests (which are more "expensive").<p/></li>
