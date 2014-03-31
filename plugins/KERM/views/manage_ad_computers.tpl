{assign var="paging_titles" value="KERM, AD Computers"}
{assign var="paging_urls" value="/?cl=kerm"}
{include file="paging.html"}


<h1>AD Computers</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="filter"> 
{$form_redir}

Customer:
<select name="filter[customer_id]" onChange="document.forms['filter'].submit()">
	<option value="">[Select one]</option>
	{html_options options=$customers_list selected=$filter.customer_id}
</select>
<p>
</form>

<table class="list" width="98%">
	<thead>
	<tr>
		<td>CN</td>
		<td>Display name</td>
		<td>Member of</td>
		<td>Operating system</td>
		<td>SP</td>
		<td align="right">Kawacs</td>
	</tr>
	</thead>
	
	{foreach from=$ad_computers item=computer}
	<tr>
		<td><a href="/?cl=kerm&op=ad_computer_view&computer_id={$computer->computer_id}&nrc={$computer->nrc}">{$computer->cn}</a></td>
		<td>{$computer->display_name}</td>
		<td>
			{$computer->member_of|replace:" , ":"<br>"}
		</td>
		<td>{$computer->operating_system}</td>
		<td>{$computer->operating_system_sp}</td>
		<td align="right">
			<a href="/?cl=kawacs&op=computer_view&id={$computer->computer_id}">#&nbsp;{$computer->computer_id}</a>
		</td> 
	</tr>
	{foreachelse}
	<tr>
		<td colspan="5">[No AD Computers]</td>
	</tr>
	{/foreach}

</table>
<p>
