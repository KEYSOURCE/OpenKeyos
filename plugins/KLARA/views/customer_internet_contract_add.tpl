{assign var="paging_titles" value="KLARA, Customer Internet Contracts, Add Contract"}
{assign var="paging_urls" value="/?cl=klara, /?cl=klara&op=manage_customer_internet_contracts"}
{include file="paging.html"}

<script language="JavaScript" src="/javascript/CalendarPopup.js" type="text/javascript"></script>

<h1>Add Customer Internet Contract</h1>

<p class="error">{$error_msg}</p>

<form action="" method="post" name="add_frm">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="140">Customer:</td>
		<td width="40%" class="post_highlight">{$customer->name} ({$customer->id})</td>
		<td width="140"> </td>
		<td> </td>
	</tr>
	</thead>
	
	<tr>
		<td class="highlight">Provider/Contract:</td>
		<td class="post_highlight">
			<select name="contract[contract_id]" style="width:250px;">
				<option value="">[Select]</option>
				{html_options options=$contracts_list selected=$contract->contract_id}
			</select>
		</td>
		<td class="highlight">Line type:</td>
		<td class="post_highlight">
			<select name="contract[line_type]">
				<option value="">[Select]</option>
				{html_options options=$LINE_TYPES selected=$contract->line_type}
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Client number:</td>
		<td class="post_highlight">
			<input type="text" name="contract[client_number]" value="{$contract->client_number}" size="40"/>
		</td>
		<td class="highlight">ADSL line number:</td>
		<td class="post_highlight">
			<input type="text" name="contract[adsl_line_number]" value="{$contract->adsl_line_number}" size="40"/>
		</td>
	</tr>
	<tr>
		<td class="highlight">Start date:</td>
		<td class="post_highlight">
	
			<input type="text" size="12" name="contract[start_date]" 
				value="{if $contract->start_date}{$contract->start_date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"
			/>
			<a href="#" onclick="showCalendarSelector('add_frm', 'contract[start_date]', 'anchor_start_date'); return false;" id="anchor_start_date"
				><img src="/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle"/></a>
		</td>
		<td class="highlight">End date:</td>
		<td class="post_highlight">
			<input type="text" size="12" name="contract[end_date]" 
				value="{if $contract->end_date}{$contract->end_date|date_format:$smarty.const.DATE_FORMAT_SELECTOR}{/if}"/>
			<a href="#" onclick="showCalendarSelector('add_frm', 'contract[end_date]', 'anchor_end_date'); return false;" id="anchor_end_date"
				><img src="/images/icon_cal.gif" alt="calendar" border="0" style="vertical-align: middle"/></a>
		</td>
	</tr>
	<tr>
		<td class="highlight">Contract active:</td>
		<td class="post_highlight">
			<select name="contract[is_closed]">
				<option value="0">Active</option>
				<option value="1" {if $contract->is_closed}selected{/if}>Closed</option>
			</select>
		</td>
		<td class="highlight">Notice period</td>
		<td class="post_highlight">
			<select name="contract[notice_months]">
				<option value="0">[Not specified]</option>
				<option value="1" {if $contract->notice_months==1}selected{/if}>1 month</option>
				<option value="2" {if $contract->notice_months==2}selected{/if}>2 months</option>
				<option value="3" {if $contract->notice_months==3}selected{/if}>3 months</option>
				<option value="6" {if $contract->notice_months==6}selected{/if}>6 months</option>
				<option value="9" {if $contract->notice_months==9}selected{/if}>9 months</option>
				<option value="12" {if $contract->notice_months==12}selected{/if}>12 months</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td colspan="4"><h2>Technical details</h2></td>
	</tr>
	<tr>
		<td class="highlight">Speed maxim:</td>
		<td class="post_highlight">
			Down: <input type="text" name="contract[speed_max_down]" value="{$contract->speed_max_down}" size="6" />KB /
			Up: <input type="text" name="contract[speed_max_up]" value="{$contract->speed_max_up}" size="6" /> KB
		</td>
		<td class="highlight">Speed guaranteed:</td>
		<td class="post_highlight">
			Down: <input type="text" name="contract[speed_guaranteed_down]" value="{$contract->speed_guaranteed_down}" size="6" />KB /
			Up: <input type="text" name="contract[speed_guaranteed_up]" value="{$contract->speed_guaranteed_up}" size="6" />KB
		</td>
	</tr>
	<tr>
		<td class="highlight">Contract or login:</td>
		<td class="post_highlight">
			<input type="text" name="contract[contract_or_login]" value="{$contract->contract_or_login}" size="40"/>
		</td>
		<td class="highlight">Password:</td>
		<td class="post_highlight">
			<input type="text" name="contract[password]" value="{$contract->password}" size="40"/>
		</td>
	</tr>
	<tr>
		<td class="highlight">IP range:</td>
		<td class="post_highlight">
			<input type="text" name="contract[ip_range]" value="{$contract->ip_range}" size="40"/>
		</td>
		<td class="highlight">IP address:</td>
		<td class="post_highlight">
			<input type="text" name="contract[ip_address]" value="{$contract->ip_address}" size="40"/>
		</td>
	</tr>
	
	<tr>
		<td rowspan="5" class="highlight">Comments:</td>
		<td rowspan="5" class="post_highlight">
			<textarea name="contract[comments]" rows="9" cols="60">{$contract->comments|escape}</textarea>
		</td>
		
		<td class="highlight">Lan - IP:</td>
		<td class="post_highlight">
			<input type="text" name="contract[lan_ip]" value="{$contract->lan_ip}" size="40"/>
		</td>
	</tr>
	
	<tr>
		<td class="highlight">Netmask:</td>
		<td class="post_highlight">
			<input type="text" name="contract[netmask]" value="{$contract->netmask}" size="40"/>
		</td>
	</tr>
	<tr>
		<td class="highlight">Has router:</td>
		<td class="post_highlight">
			<select name="contract[has_router]">
				<option value="0">No</option>
				<option value="1" {if $contract->has_router}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Has SMTP feed:</td>
		<td class="post_highlight">
			<select name="contract[has_smtp_feed]">
				<option value="0">No</option>
				<option value="1" {if $contract->has_smtp_feed}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight">Managed by Keysource:</td>
		<td class="post_highlight">
			<select name="contract[is_keysource_managed]">
				<option value="0">No</option>
				<option value="1" {if $contract->is_keysource_managed}selected{/if}>Yes</option>
			</select>
		</td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>
