{assign var="update_id" value=$update->id}
{assign var="paging_titles" value="KAWACS, Manage Kawacs Agent Updates, Edit, Add Pre-release Computer"}
{assign var="paging_urls" value="/kawacs, /kawacs/manage_kawacs_updates, /kawacs/kawacs_udpate_edit/"}
{include file="paging.html"}


<h1>Add Pre-release Computer</h1>

<p class="error">{$error_msg}</p>

<p>Specify a computer which should get a pre-release update of Kawacs Agent.</p>

<form action="" method="POST" name="data_frm">
{$form_redir}

Customer:
<select name="customer_id" onchange="document.forms['data_frm'].submit();">
	<option value="">[Select customer]</option>
	{html_options options=$customers_list selected=$customer_id}
</select>
<p/>

Computer:
{if $customer_id}
	<select name="computer_id">
		<option value="">[Select computer]</option>
		{html_options options=$computers_list}
	</select>
{else}
	<font class="light_text">[Please select a customer first]</font>
{/if}
<p/>

{if $customer_id}
<input type="submit" name="save" value="Add computer" class="button" />
{/if}

<input type="submit" name="cancel" value="Close" class="button" />

</form>