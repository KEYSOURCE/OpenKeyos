{assign var="paging_titles" value="KAWACS, Customers Allowed IPs, Add Allowed IP"}
{assign var="paging_urls" value="/kawacs, /kawacs/customers_allowed_ips"}
{include file="paging.html"}


<script language="JavaScript" type="text/javascript">
//<![CDATA[

{literal}
function do_select_customer (c_id)
{
	var frm = document.forms['frm_t'];
	var cust_lst = frm.elements['allowed_ip[customer_id]'];
	
	for (var i=0; i<cust_lst.options.length; i++)
	{
		if (cust_lst.options[i].value == c_id)
		{
			cust_lst.options[i].selected = true;
			break;
		}
	}
	return false;
}
{/literal}

//]]>
</script>

<h1>Add Customer Allowed IPs</h1>

<p class="error">{$error_msg}</p>

<p>
You can specify below a public remote IP address from which Kawacs Agent is
allowed to submit data for computers belonging to a specific customer.
<p/>
<p>
<b>IMPORTANT NOTE:</b>
When you assign an IP to a customer, all new computers connecting to Keyos
through that IP will be <b>automatically</b> assigned to that customer. So
please make sure that the IP is indeed allowed for that customer.
</p>

<form action="" method="POST" name="frm_t">
{$form_redir}
<table class="list" width="80%">
	<thead>
	<tr>
		<td colspan="2">Enter allowed IP</td>
	</tr>
	</thead>
	
	<tr>
		<td width="120" class="highlight">Customer:</td>
		<td class="post_highlight">
			<select name="allowed_ip[customer_id]">
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$allowed_ip->customer_id}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Remote IP address:</td>
		<td class="post_highlight">
			<input type="text" name="allowed_ip[remote_ip]" size="25" value="{$allowed_ip->remote_ip|escape}" />
		</td>
	</tr>
	{if count($existing_customers) > 0}
	<tr>
		<td class="highlight"> </td>
		<td class="post_highlight">
			Current customers with computers reporting through <b>{$allowed_ip->remote_ip|escape}</b> - click to select:
			<p/>
			{foreach from=$existing_customers key=c_id item=comps_count}
				<a href="#" onclick="return do_select_customer({$c_id});">{$customers_list.$c_id}</a>
				({$comps_count} computers)
				<br/>
			{/foreach}
		</td>
	</tr>
	{/if}
</table>
<p/>
<input type="submit" name="save" value="Add IP Address" class="button" />
<input type="submit" name="cancel" value="Cancel" class="button" />

</form>