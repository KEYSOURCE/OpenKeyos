{assign var="paging_titles" value="KALM, Manage Software, Customers"}
{assign var="paging_urls" value="/?cl=kalm"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var customers_count = {$all_customers|@count};

{literal}
function switch_show (must_show)
{
	if (must_show)
	{
		elm = document.getElementById ('link_show');
		elm.style.display = 'none';
		elm = document.getElementById ('link_hide');
		elm.style.display = '';
		
		for (i=0; i<customers_count; i++)
		{
			elm = document.getElementById ('div_comp_summary_'+i);
			elm.style.display = 'none';
			elm = document.getElementById ('div_comp_all_'+i);
			elm.style.display = '';
		}
	}
	else
	{
		elm = document.getElementById ('link_show');
		elm.style.display = '';
		elm = document.getElementById ('link_hide');
		elm.style.display = 'none';
		
		for (i=0; i<customers_count; i++)
		{
			elm = document.getElementById ('div_comp_summary_'+i);
			elm.style.display = '';
			elm = document.getElementById ('div_comp_all_'+i);
			elm.style.display = 'none';
		}
	}
	
	return false;
}

function switch_one (must_show, idx)
{
	if (must_show)
	{
		elm = document.getElementById ('div_comp_summary_'+idx);
		elm.style.display = 'none';
		elm = document.getElementById ('div_comp_all_'+idx);
		elm.style.display = '';
	}
	else
	{
		elm = document.getElementById ('div_comp_summary_'+idx);
		elm.style.display = '';
		elm = document.getElementById ('div_comp_all_'+idx);
		elm.style.display = 'none';
	}
	
	return false;
}


{/literal}

//]]>
</script>


<h1>Customers using : {$software->name}</h1>

<p class="error">{$error_msg}</p>

<table width="80%">
	<tr>
		<td width="50%">
			<a href="/?cl=kalm&amp;op=manage_software">&#0171 Back</a>
		</td>
		<td width="50%" nowrap="nowrap" align="right">
			<a href="" id="link_show" onclick="return switch_show(true);">[ Show computers ]</a>
			<a href="" id="link_hide" style="display:none;" onclick="return switch_show(false);">[ Hide computers ]</a>
		</td>
	</tr>
</table>
<p/>


<table class="list" width="80%">
	<thead>
		<tr>
			<td width="1%">ID</td>
			<td width="39%">Customer</td>
			<td width="20%">License defined</td>
			<td width="20%">Computers</td>
			<td width="20%"> </td>
		</tr>
	</thead>
	
	{assign var="idx" value=0}
	{foreach from=$all_customers key=customer_id item=customer_name}
		<tr>
			<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customer_id}</a></td>
			<td><a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customer_name}</a></td>
			
			<td>
				{if $customers.$customer_id}Yes{/if}
			</td>
			<td nowrap="nowrap">
				<div id="div_comp_summary_{$idx}">
					<a href="" onclick="return switch_one(true, {$idx});">{$all_computers.$customer_id|@count} computers &#0187;</a>
				</div>
				
				<div id="div_comp_all_{$idx}" style="display:none;">
					<a href="" onclick="return switch_one(false, {$idx});">&#0171; {$all_computers.$customer_id|@count} computers </a><br/><br/>
					
					{foreach from=$all_computers.$customer_id key=computer_id item=computer_name}
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">#{$computer_id}:&nbsp;{$computer_name}</a><br/>
					{/foreach}
				</div>
				<!-- {$idx++} -->
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kalm&amp;op=manage_licenses&amp;customer_id={$customer_id}">Licenses &#0187;</a>
			</td>
		</tr>
	
	{foreachelse}
		<tr>
			<td colspan="3">[No customers using this software]</td>
		</tr>
	{/foreach}
</table>
<p/>
<a href="/?cl=kalm&amp;op=manage_software">&#0171 Back</a>
<p/>
