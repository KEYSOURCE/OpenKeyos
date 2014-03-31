{assign var="paging_titles" value="Customers, Customer Computers Report"}
{assign var="paging_urls" value="/?cl=customer"}
{include file="paging.html"}

<h1>Customer Computers Report</h1>
<p>
<font class="error">{$error_msg}</font>
<p>

<form action="" method="POST" name="report_frm">
{$form_redir}


{if !$customer}
	<!-- No customer selected -->
	Customer:<br>
	<select name="filter[customer_id]">
		<option value="">[Select]</option>
		{html_options options=$customers_list selected=$current_locked_customer_id}
	</select>
	
	<p>
	<input type="submit" name="select" value="Select">

{else}
    <table width="98%" class="list">
		<thead>
		<tr>
			<td>Customer:</td>
			<td class="post_highlight" colspan="3">
				{$customer->name} ({$customer->id})
				&nbsp;&nbsp;&nbsp;
				<a href="/?cl=customer&op=customer_computers_report&change_customer=1">Change &#0187;</a>
			</td>
		</tr>
		</thead>
		
		<tr>
			<td width="15%" class="highlight">Title:</td>
			<td width="35%" class="post_highlight">
				<input type="text" name="filter[title]" value="{$customer->name} Computers Report" size="30">
			</td>
                        <td colspan="2">&nbsp;</td>
                </tr>
                
                <tr>
                        <td colspan=4 class="highlight"><b>Items</b></td>
                </tr>
                {foreach from=$filter.report_items item="micat"}                
                <tr>
                    <td width="15%" class="highlight">{$micat.name}</td>
                    <td colspan=3>
                        <table>
                        {assign var="i" value=0}
                        {foreach from=$micat.items item="mitem"}
                            {if $i==0}<tr>{/if}
                            {if $i<5}
                                <td>
                                <input type="checkbox" name="filter[select_items][]" value="{$mitem.id}"  {if $mitem.select==1}checked{/if} />{$mitem.name}&nbsp;&nbsp;
                                </td>
                                {assign var="i" value=$i+1}    
                            {else}
                                <td>
                                <input type="checkbox" name="filter[select_items][]" value="{$mitem.id}"  {if $mitem.select==1}checked{/if} />{$mitem.name}&nbsp;&nbsp;
                                </td></tr>
                                {assign var="i" value=0}
                            {/if}
                            
                        {/foreach}
                        </table>
                    </td>
                </tr>
                {/foreach}
    </table>
    <p />
    <input type="submit" name="generate" value="Generate" />
{/if}
</form>