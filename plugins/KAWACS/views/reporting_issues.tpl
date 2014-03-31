{assign var="paging_titles" value="KAWACS, Computers Reporting Issues"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}

<h1>Computers Reporting Issues</h1>

<p class="error">{$error_msg}</p>


<h3>Conflicting MAC Addresses</h3>
{if count($conflicting_macs) == 0}
	<p class="light_text">[No problems detected]</p>
{else}
	<p>These are different computers in Keyos which are identified by the same MAC address. Usually this means
	that either a computer has been recorded twice in Keyos by accident, or that there is a problem with the
	identification of computers in Keyos or Kawacs Agent.</p>
	
	<p>To solve these issues, check the reporting settings for the Agent. If the computers are indeed the same,
	you can either merge them or delete the one which is not needed. If the problem keeps re-appearing, the code
	might require updating.</p>
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="160">MAC Address</td>
			<td>Computers</td>
		</tr>
		</thead>
		
		{foreach from=$conflicting_macs key=mac item=computers}
		<tr>
			<td><b>{$mac|escape}</b></td>
			<td>
				{foreach from=$computers item=computer}
				<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}">#{$computer->id}: {$computer->netbios_name}</a>,
				Customer: {$computer->customer_name} ({$computer->customer_id})<br/>
				{/foreach}
			</td>
		</tr>
		{/foreach}
	</table>
	<p/>
{/if}


<h3>Conflicting Names</h3>
{if count($conflicting_names) == 0}
	<p class="light_text">[No problems detected]</p>
{else}
	<p>These are different computers which are reported to have the same names. These could be legitimate names, but could also mean that 
	the same computer is recorded more than once in Keyos.</p>
	
	<p>If these are legitimate names duplications, you can add them to the list of valid duplicate names. If the computers are in fact the
	same, you can either merge them or delete the ones which is not needed.</p>
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="160">Name</td>
			<td>Computers</td>
			<td width="120"> </td>
		</tr>
		</thead>
		
		{foreach from=$conflicting_names key=name item=computers}
		<tr>
			<td><b>{$name|escape}</b></td>
			<td>
				{foreach from=$computers item=computer}
					<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}">#{$computer->id}: {$name|escape}</a>,
					Customer: {$computer->customer_name|escape} ({$computer->customer_id})<br/>
				{/foreach}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kawacs&amp;op=valid_dup_name_add&amp;dup_name={$name|escape}">Add to valid duplicate names &#0187;</a>
			</td>
		</tr>
		{/foreach}
	</table>
	<p/>
{/if}


<h3>Name Swingers</h3>
{if count($name_swingers) == 0}
	<p class="light_text">[No problems detected]</p>
{else}
	<p>These are computers for which the reported names has changed multiple times, which usually indicates that different computers are 
	seen by Keyos as being the same.</p>
	
	<p>To fix this, check the reporting settings for the computers in Kawacs Agent. Once the source of the problem has been eliminated,
	you can clean the log of the invalid names.</p>
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="160">Computer</td>
			<td>Reported names</td>
			<td width="120">
		</tr>
		</thead>
		
		{foreach from=$name_swingers key=computer_id item=names}
		<tr>
			<td><a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">#{$computer_id}: {$names.0}</a></td>
			<td>
				{foreach from=$names item=name}{$name|escape} <br/>{/foreach}
			</td>
			<td align="right" nowrap="nowrap">
				<a href="/?cl=kawacs&amp;op=computer_name_swings_clean&amp;id={$computer_id}">Clean logs &#0187;</a>
			</td>
		</tr>
		{/foreach}
	</table>
	<p/>
{/if}


<h3>Conflicting IPs</h3>
{if count($conflicting_ips) == 0}
	<p class="light_text">[No problems detected]</p>
{else}
	<p>These are remote public IPs through which Kawacs Agents reported data but the
	IPs are not in the allowed remote IPs list for those respective customers.<br/>
	The table below shows each of the conflicting remote IPs and the customers and computers
	which were found to be reporting through thoses IPs.</p>
	
	<p>If the IPs are legitimate for these customers, add them to the allowed IPs list for those
	customers. If the problem is that the computers are assigned to the wrong customers, 
	reassign them to the correct customers.</p>
	<p/>
	
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="160">Public IP / Customer</td>
			<td>Computers reporting through this IP</td>
		</tr>
		</thead>
		
		{foreach from=$conflicting_ips key=ip item=conflicting_ip}
			<tr class="head">
				<td>IP: {$ip}</td>
				<td>
					{if isset($allowed_ips_list.$ip)}
						Currently allowed for:
						{foreach from=$allowed_ips_list.$ip item=allowed_customer_id name=allowed_cust_ip}
							#{$allowed_customer_id}: {$customers_list.$allowed_customer_id}
						{/foreach}
					{else}
						[Not assigned to any customer yet]
					{/if}
					&nbsp;&nbsp;|&nbsp;&nbsp;
					<a href="/?cl=kawacs&amp;op=customer_allowed_ip_add&amp;remote_ip={$ip}">Add to allowed list &#0187;</a>
				</td>
			</tr>
			{foreach from=$conflicting_ip key=customer_id item=computers}
				<tr>
					<td nowrap="nowrap">
						<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">#{$customer_id}: {$customers_list.$customer_id}</a><br/>
						{$computers|@count} computer{if count($computers)>1}s{/if}
					</td>
					<td>
						{foreach from=$computers item=computer name=ips_computers}
							<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer->id}"
							>#{$computer->id}:&nbsp;{$computer->netbios_name}</a>{if !$smarty.foreach.ips_computers.last},&nbsp;&nbsp;{/if}
						{/foreach}
					</td>
				</tr>
			{/foreach}
		{/foreach}
	
	</table>
	<p/>
{/if}

<a name="late_contacts"></a>
<h3>Discoveries Without Keyos Contact</h3>
{if count($conflicting_discoveries) == 0}
	<p class="light_text">[No problems detected]</p>
{else}
	<p>
	Below you have the computers found by network discoveries and matched to Keyos computers for which the last discovery date
	is more recent with more than {$smarty.const.DISCOVERY_REPORTING_ISSUE_INTERVAL/3600} hours than the last Kawacs Agent report.<br/>
	This usually means that although the computer is running (since it was found by the discovery), the Kawacs Agent is stopped
	or otherwise unable to send reports.
	</p>
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="160">Computer</td>
			<td>Last discovered</td>
			<td>Last Kawacs Agent report</td>
		</tr>
		</thead>
	{foreach from=$conflicting_discoveries item=conflict}
	<tr>
		<td>
			<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$conflict.computer->id}"
			>#{$conflict.computer->id}: {$conflict.computer->netbios_name}</a>
		</td>
		<td nowrap="nowrap">
			<a href="/?cl=discovery&amp;op=discovery_details&amp;id={$conflict.discovery->id}"
			>{$conflict.discovery->last_discovered|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</a>
		</td>
		<td nowrap="nowrap">
			{$conflict.computer->last_contact|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}
		</td>
	</tr>
	{/foreach}
	</table>
{/if}