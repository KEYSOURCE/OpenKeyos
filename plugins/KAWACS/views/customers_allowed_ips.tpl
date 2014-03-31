{assign var="paging_titles" value="KAWACS, Customers Allowed IPs"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function do_add_ip ()
{
	var frm = document.forms['filter_frm'];
	var cust_lst = frm.elements['filter[customer_id]'];
	var c_cust_id = cust_lst.options[cust_lst.selectedIndex].value;
	var url = '/kawacs/customer_allowed_ip_add';
	
	if (c_cust_id > 0) url = url + '?customer_id=' + c_cust_id;
	
	document.location = url;
	return false;
}
{/literal}

//]]>
</script>

<h1>Customers Allowed IPs</h1>

<p class="error">{$error_msg}</p>

<p>
These are the remote public IP addresses through which Kawacs Agent is allowed to submit
data for the computers belonging to these customers. If data for a customer is received
through an IP not in the allowed list, the data will still be accepted, but a notification
will be raised.<br/>
IMPORTANT NOTE: These IPs are also used for automatically determining the customer to 
which new computers are assigned.
</p>

<form action="" method="POST" name="filter_frm">
{$form_redir}
Customer:
<select name="filter[customer_id]" onchange="document.forms['filter_frm'].submit();">
	<option value="">[All customers]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
<a href="#" onclick="return do_add_ip();">Add new allowed IP &#0187;</a>

</form>


<p/>
<table class="list" width="98%">
	<thead>
	<tr>
		<td width="200">Customer</td>
		<td width="140">Remote IP</td>
		<td>Computers</td>
		<td width="50"> </td>
	</tr>
	</thead>
	
	{assign var="last_customer_id" value=""}
	{foreach from=$allowed_ips item=allowed_ip}
	<tr>
		<td>
			{assign var="customer_id" value=$allowed_ip->customer_id}
			{if $last_customer_id!=$customer_id}
                {assign var="p" value="id:"|cat:$customer_id}
				<a href="{'customer'|get_link:'customer_edit':$p:'template'}">{$customers_list_all.$customer_id}</a>
				{assign var="last_customer_id" value=$customer_id}
			{/if}
		</td>
		<td>
			{$allowed_ip->remote_ip|escape}
		</td>
		<td>
			{assign var="remote_ip" value=$allowed_ip->remote_ip}
			{if isset($ips_computers_list.$remote_ip)}
				{foreach from=$ips_computers_list.$remote_ip item=computer name="computers"}
					{if $computer->customer_id==$customer_id or !in_array($computer->customer_id,$allowed_ips_list.$remote_ip)}

                    {assign var="p" value="id:"|cat:$computer->id}
					<a href="{'kawacs'|get_link:'computer_view':$p:'template'}"
					{if $computer->customer_id!=$customer_id}class="error"{/if}
					>#{$computer->id}:&nbsp;{$computer->netbios_name}</a>{if !$smarty.foreach.computers.last}{if $filter.customer_id}<br/>{else}, {/if}{/if}
					
					{/if}
				{/foreach}
			{else}--{/if}
		</td>
		<td align="right" nowrap="nowrap">
            {assign var='p' value='id:'|cat:$allowed_id->id}
			<a href="{'kawacs'|get_link:'customer_allowed_id_delete':$p:'template'}"
			onclick="return confirm('Are you really sure you want to remove the IP {$allowed_ip->remote_ip}?');"
			>Remove &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4" class="light_text">[No allowed IPs defined]</td>
	</tr>
	{/foreach}
</table>