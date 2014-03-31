{assign var="paging_titles" value="Customers, Customers without CC Recipients"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<script language="JavaScript">
//<![CDATA[

{literal}
function doEditRecipients ()
{
	elm_list = document.getElementById ('customers');
	cust_id = elm_list.options[elm_list.selectedIndex].value;
	if (isNaN(parseInt(cust_id))) alert ('Please select a customer first');
	else window.location = '/?cl=customer&op=cc_recipients_edit&customer_id=' + cust_id;
}
{/literal}

//]]>
</script>

<h1>Manage Customers without CC Recipients</h1>

<p class="error">{$error_msg}</p>

<p>Below you can set for each customer the users which should be added by 
default as CC recipients to any newly created tickets.</p>

Customer:
<select name="customers" id="customers">
	<option value="">[Select customer]</option>
	{html_options options=$customers_list}
</select>
&nbsp;&nbsp;&nbsp;
<a href="#" onclick="doEditRecipients(); return false;">Edit CC Recipients &#0187;</a>
&nbsp;&nbsp;&nbsp;
<a href="/?cl=customer&op=manage_cc_recipients">Manage CC Recipients &#0187;</a>
<p/>

<table class="list" width="70%">
	<thead>
	<tr>
		<td width="30%">Customer</td>
		<td width="60%">Default CC recipients</td>
		<td> </td>
	</tr>
	</thead>
	
	{foreach from=$all_not_recipients item=customer}
	<tr>
		<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer.id}">{$customer.name|escape}</a></td>
		<td>
			--
		</td>
		<td align="right" nowrap="nowrap">
			<a href="/?cl=customer&amp;op=cc_recipients_edit&amp;customer_id={$customer.id}">Edit &#0187;</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="3" class="light_text">[All customers have at least one CC recipient]</td>
	</tr>
	{/foreach}
</table>
