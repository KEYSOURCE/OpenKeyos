{assign var="paging_titles" value="KLARA, Access Information"}
{assign var="paging_urls" value="/?cl=kawacs"}
{include file="paging.html"}



<script language="JavaScript">
{if $add_url}
var add_url = "{$add_url}";
{/if}

{literal}
function do_add_remote ()
{
	frm = document.forms['filter'];
	elm = frm.elements['public_ips'];
	
	public_ip = elm.options[elm.selectedIndex].value;
	if (public_ip == '-1')
	{
		alert ('Please select the public IP for which you want to define the information.');
	}
	else
	{
		url = add_url + "&public_ip=" + public_ip;
		window.location = url;
	}
	
	return false;
}
{/literal}
</script>


<h1>Access Information</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="filter"> 
{$form_redir}
<table width="98%">
	<tr>
		<td width="50%">
			<b>Customer:</b>
			
			<select name="filter[customer_id]"  
				onChange="document.forms['filter'].elements['do_filter_hidden'].value=1; document.forms['filter'].submit();"
			>
				<option value="">[Select customer]</option>
				{html_options options=$customers_list selected=$filter.customer_id}
			</select>
			<input type="hidden" name="do_filter_hidden" value="0">
		</td>
		<td width="50%" align="right">
			{if $customer->id}
				<select name="public_ips">
					<option value="-1">[Select public IP]</option>
					{html_options values=$customer_ips_list output=$customer_ips_list}
					<option value="">[Other IP]</option>
				</select>
				<a href="" onClick="return do_add_remote();">Define&nbsp;&#0187;</a>
			{/if}
		</td>
	</tr>
</table>
</form>
<p/>

{if $customer->id}
                    <select onchange="window.location.hash=this.options[this.selectedIndex].value">
                        <option value="computer_access_info">Computer Access Information</option>
                        <option value="network_passwords">Network passwords - all computers</option>
                        <option value="periph_net_access">Peripherals Network Access</option>
                        <option value="periph_web_access">Peripherals Web Access</option>
                        <option value="web_access">Web Access Credentials</option>
                    </select>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="20">ID</td>
			<td width="20%">Public IP</td>
			<td width="20%">Port forwarding (plink)</td>
			<td> </td>
		</tr>
		</thead>

		{foreach from=$remote_ips item=ip}
			<tr>
				<td><a href="/?cl=klara&op=remote_access_edit&id={$ip->id}{$do_filter_url}">{$ip->id}</a></td>
				<td><a href="/?cl=klara&op=remote_access_edit&id={$ip->id}{$do_filter_url}">{$ip->public_ip}</a></td>
				<td nowrap="nowrap">
					{if $ip->has_port_forwarding}
						Port: {$ip->pf_port},&nbsp;&nbsp;
						Login: {$ip->pf_login} / {$ip->pf_password}
					{else}
						--
					{/if}
				</td>
				<td align="right">
					<a href="/?cl=klara&op=remote_access_delete&id={$ip->id}{$do_filter_url}"
						onClick="return confirm('Are you sure you want to delete this definitions?');"
					>Delete &#0187;</a>
				</td>
			</tr>
		
		{foreachelse}
			<tr>
				<td colspan="4">[No information defined yet]</td>
			</tr>
		{/foreach}
		
	</table>
	
	<h2 ><a name="computer_access_info"></a>Computers Access Information</h2>
	</p>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="20%">Computer</td>
			<td width="3%">&nbsp;&nbsp;</td>
			<td width="37%">
				Services |
				<a href="/?cl=klara&op=computer_remote_service_add&customer_id={$customer->id}{$do_filter_url}">Add service &#0187;</a>	
			</td>
			<td width="3%">&nbsp;&nbsp;</td>
			<td width="37%">
				Passwords |
				<a href="/?cl=klara&op=computer_password_add&customer_id={$customer->id}{$do_filter_url}">Add password &#0187;</a>	
			</td>
		</tr>
		</thead>
		
		{assign var="computers_listed" value=0}
		{foreach from=$computers_list key=computer_id item=computer_name}
		{if $computers_services.$computer_id or $computers_passwords.$computer_id}
			{assign var="computers_listed" value=1}
			<tr>
				<td><a href="/?cl=kawacs&op=computer_view&id={$computer_id}">{$computer_name}</a></td>
			
				<td> </td>
				<td>
					{if $computers_services.$computer_id}
						<table width="100%">
							{foreach from=$computers_services.$computer_id item=remote_service}
							{assign var="service_id" value=$remote_service->service_id}
							<tr>
								<td>
									<a href="/?cl=klara&op=computer_remote_service_edit&id={$remote_service->id}{$do_filter_url}">
									{if !$remote_service->is_custom}
										{$REMOTE_SERVICE_NAMES.$service_id}&nbsp;:&nbsp;{$remote_service->port}</a>
									{else}
										{$remote_service->name}&nbsp;:&nbsp;{$remote_service->port}</a>
										{if $remote_service->is_web}
											(Web)
										{/if}
									{/if}
									{if $remote_service->comments}
										- {$remote_service->comments|escape|nl2br}
									{/if}
								<td align="right">
									<a href="/?cl=klara&op=computer_remote_service_delete&id={$remote_service->id}{$do_filter_url}"
										onClick="return confirm('Are you really sure you want to delete this service?');"
									>Delete&nbsp;&#0187;</a>
								</td>
							</tr>
							{/foreach}
						</table>
						<br/>
					{/if}
				</td>
				
				<td> </td>
				<td>
					{if $computers_passwords.$computer_id}
						<table width="100%">
							{foreach from=$computers_passwords.$computer_id item=password}
							<tr>
								<td>
									<a href="/?cl=klara&op=computer_password_edit&id={$password->id}{$do_filter_url}"
									>{$password->login} / {$password->password}</a>
									{if $password->comments}
										- {$password->comments|escape|nl2br}
									{/if}
								</td>
								<td align="right">
									<a href="/?cl=klara&op=computer_password_expire&id={$password->id}{$do_filter_url}"
									>Expire&nbsp;&#0187;</a><br/>
									<a href="/?cl=klara&op=computer_password_delete&id={$password->id}{$do_filter_url}"
										onClick="return confirm('Are you really sure you want to delete this password?');"
									>Delete&nbsp;&#0187;</a>
								</td>
							</tr>
							{/foreach}
						</table>
						<br/>
					{/if}
					{if in_array($computer_id, $computers_with_expired_passwords) }
						[ <a href="/?cl=klara&op=computer_passwords_expired&computer_id={$computer_id}{$do_filter_url}"
						>See expired passwords&#0187;</a> ]
					{/if}
				</td>
			</tr>
		{/if}
		{/foreach}
	
		{if !$computers_listed}
		<tr>
			<td colspan="5" class="light_text">[No computer information defined yet]</td>
		</tr>
		{/if}
		
		<!-- Show network passwords for this customer -->
		{if $computers_passwords.0}
		<tr>
			<td colspan="4"><a name="network_passwords"></a><b>[Network passwords - all computers]</b></td>
			<td>
				<table width="100%">
					{foreach from=$computers_passwords.0 item=password}
					<tr>
						<td>
							<a href="/?cl=klara&op=computer_password_edit&id={$password->id}{$do_filter_url}"
							>{$password->login} / {$password->password}</a>
							{if $password->comments}
								- {$password->comments|escape|nl2br}
							{/if}
						</td>
						<td align="right">
							<a href="/?cl=klara&op=computer_password_expire&id={$password->id}{$do_filter_url}"
							>Expire&nbsp;&#0187;</a><br/>
							<a href="/?cl=klara&op=computer_password_delete&id={$password->id}{$do_filter_url}"
								onClick="return confirm('Are you really sure you want to delete this password?');"
							>Delete&nbsp;&#0187;</a>
						</td>
					</tr>
					{/foreach}
				</table>
				<br/>
				{if in_array(0, $computers_with_expired_passwords) }
					[ <a href="/?cl=klara&amp;op=computer_passwords_expired&amp;customer_id={$customer->id}&amp;computer_id=0{$do_filter_url}"
					>See expired passwords&#0187;</a> ]
				{/if}
			</td>
		</tr>
		{/if}
		
		
		
	</table>
	
	<h2 ><a name="periph_net_access"></a>Peripherals - Network Access</h2>
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="30%">Peripheral</td>
			<td width="15%">Class</td>
			<td width="15%">IP address</td>
			<td width="10%">Port</td>
			<td width="15%">Login</td>
			<td width="15%">Password</td>
		</tr>
		</thead>
		
		{foreach from=$peripherals item=peripheral}
		{if $peripheral->class_def->use_net_access}
			<tr>
				<td>
					<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral->id}&amp;returl={$ret_url}"
					>{$peripheral->name} ({$peripheral->id})</a>
				</td>
				<td>{$peripheral->class_def->name}</td>
				<td>
					{if $peripheral->class_def->net_access_ip_field}
						{assign var="ip_field_id" value=$peripheral->class_def->net_access_ip_field}
						{assign var="ip_field_idx" value=$peripheral->class_def->field_ids_idx.$ip_field_id}
						{$peripheral->values.$ip_field_idx}
					{else}
						<font class="light_text">-</font>
					{/if}
				</td>
				<td>
					{if $peripheral->class_def->net_access_port_field}
						{assign var="port_field_id" value=$peripheral->class_def->net_access_port_field}
						{assign var="port_field_idx" value=$peripheral->class_def->field_ids_idx.$port_field_id}
						{$peripheral->values.$port_field_idx}
					{else}
						<font class="light_text">-</font>
					{/if}
				</td>
				<td>
					{if $peripheral->class_def->net_access_login_field}
						{assign var="login_field_id" value=$peripheral->class_def->net_access_login_field}
						{assign var="login_field_idx" value=$peripheral->class_def->field_ids_idx.$login_field_id}
						{$peripheral->values.$login_field_idx}
					{else}
						<font class="light_text">-</font>
					{/if}
				</td>
				<td>
					{if $peripheral->class_def->net_access_password_field}
						{assign var="password_field_id" value=$peripheral->class_def->net_access_password_field}
						{assign var="password_field_idx" value=$peripheral->class_def->field_ids_idx.$password_field_id}
						{$peripheral->values.$password_field_idx}
					{else}
						<font class="light_text">-</font>
					{/if}
				</td>
			</tr>
		{/if}
		{/foreach}
	</table>
	
	<h2><a  name="periph_web_access"></a>Peripherals - Web Access</h2>
	<table class="list" width="98%">
		<thead>
		<tr>
			<td width="30%">Peripheral</td>
			<td width="15%">Class</td>
			<td width="55%">URL</td>
		</tr>
		</thead>
		
		{foreach from=$peripherals item=peripheral}
		{if $peripheral->class_def->use_web_access}
			<tr>
				<td>
					<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral->id}&amp;returl={$ret_url}"
					>{$peripheral->name} ({$peripheral->id})</a>
				</td>
				<td>{$peripheral->class_def->name}</td>
				<td>
					{if $peripheral->get_access_url()}
						<a href="{$peripheral->get_access_url()}">{$peripheral->get_access_url()}</a>
					{else}
						<font class="light_text">-</font>
					{/if}
				</td>
			</tr>
		{/if}
		{/foreach}
	</table>
        
                    <h2 ><a name="web_access"></a>Web Access Credentials</h2>
                    <p><a href="/?cl=klara&op=webaccess_add&customer_id={$customer->id}">Add WebAccess credentials &#0187;</a></p>
	<table class="list" width="98%">
                    <thead>
                        <tr>
                            <td width="20%">URI</td>
                            <td widht="30%">Comments</td>
                            <td width="30%">Credentials</td>
                            <td width="10%">Last modification</td>
                            <td width="10%">By</td>
                            <td width="20px">&nbsp;</td>
                            <td width="20px">&nbsp;</td>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$web_access item="wa"}
                            <tr>
                                    <td><a href="{$wa->uri}" target="_blank">{$wa->uri}</a></td>
                                    <td>{$wa->comments}</td>
                                    <td>
                                        <div style="width: 100%; min-width:  200px; height: auto;">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <td>Username</td>
                                                        <td>Password</td>
                                                        <td>Notes</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {foreach from=$wa->credentials item="cred"}
                                                    <tr>
                                                        <td>{$cred->username}</td>
                                                        <td>{$cred->password}</td>
                                                        <td>{$cred->notes}</td>
                                                    </tr>
                                                    {/foreach}
                                                 </tbody>
                                             </table>
                                        </div>
                                    </td>
                                    <td>{$wa->date_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
                                    <td>{assign var="uid" value=$wa->user_id}                                              
                                              (#{$uid}) {$users_list.$uid} </td>
                                    <td><a href="/?cl=klara&op=webaccess_edit&waid={$wa->id}">Edit &#0187;</a></td>
                                    <td><a href="/?cl=klara&op=webaccess_delete&waid={$wa->id}">Delete &#0187;</a></td>
                            </tr>
                        {/foreach}
                    </tbody>
                    </table>
{/if}