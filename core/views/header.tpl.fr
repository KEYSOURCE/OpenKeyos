<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<meta name="robots" content="noarchive,nocache" />
	 <meta HTTP-EQUIV="content-type" CONTENT="text/html; charset=ISO-8859-1">

	<title>KeyOS System</title>
	<!--
	<style type="text/css">@import url('main.css');</style>
	-->
	<link rel="stylesheet" type="text/css" href="/gen_main_css.php?id={$current_user->id}" />
    <link rel="stylesheet" type="text/css" href="/javascript/fancybox/jquery.fancybox-1.3.4.css" />
	<link rel="stylesheet" href="/js_color_picker_v2.css" type="text/css" media="screen" />
	<script language="JavaScript" src="/javascript/anchors.js" type="text/javascript"></script>
    <script type="text/javascript" language="JavaScript" src="/javascript/jquery.js"></script>
	<link rel="shortcut icon" href="/images/logo.ico" type="image/x-icon" />
        
        <script language="JavaScript" type="text/javascript">
        //<![CDATA[                        
            var main_modules = {$MAIN_MODULES};
            var main_customer_modules = {$MAIN_CUSTOMER_MODULES};
            var menu = {$MENU};                      
            var menu_customer = {$MENU_CUSTOMER};
        //]]>
        </script>
        <script language="JavaScript" type="text/javascript" src="/javascript/keyos_plugins.js"></script>
</head>
<body marginwidth="0" marginheight="0"> 
<div id="customer_plugins_menu_container">
    <div id="menu_customer_krifs_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;">
    {* nothing to display here for now *}
    </div>
</div>
<div id="plugins_menu_container">
<div id="menu_kawacs_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;">
	<a href="/kawacs/manage_computers">Computers</a>
	<a href="/kawacs/kawacs_console">KAWACS Console</a>
	<a href="/kawacs/customers_computer_count">Customer computers</a>
	
	<a href="/kawacs/manage_notifications">Notifications Status</a>
	<a href="/kawacs&op=check_rbl_listed_servers">RBL Statuses</a>
	<a href="/kawacs/computer_add">Add Computer Manually</a>
	<a href="/kawacs/manage_peripherals">Peripherals</a>
	<a href="/kawacs/manage_blackouts">Blackouts &amp; Ignored Computers</a>
	<a href="/warranties/manage_warranties">Warranties</a>
	<a href="/warranties/warranties_eow">Warranties - Out of Warranty</a>
        <a href="/warranties/online_warranty_check">Check warranties online</a>
	
	<a href="/kawacs_removed/manage_computers" id="menu_removed_devices_ca" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');">Removed Devices &#0187;</a>
	<div id="menu_removed_devices_ca_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');" style="display: none; width:240px;">
		<a href="/kawacs_removed/manage_computers">Removed Computers</a>
		<a href="/kawacs_removed/manage_peripherals">Removed Peripherals</a>
		<a href="/kawacs_removed/manage_ad_printers">Removed AD Printers</a>
	</div>
</div>	

<div id="menu_krifs_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 240px; display: none; overflow:show;">
	<a href="/krifs/manage_tickets">Tickets</a>
	<!-- added -->
	<a href="/krifs/manage_interventions" id="menu_IR_ca" onMouseOver="showSubMenu(this.id, 'menu_krifs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_krifs_ca');">Intervention Reports &#0187</a>
	<div id="menu_IR_ca_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_krifs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_krifs_ca');" style="display: none; width:240px;">
		<a href="/krifs/manage_interventions">Intervention Reports</a>
		<a href="/krifs/interventions_print_console">IR print console</a>
	</div>
	<!-- end added -->
	<a href="/krifs/manage_timesheets">Timesheets</a>
	<a href="/erp/manage_customer_orders">Customer Orders / Subscriptions</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/krifs/manage_tasks">Tasks Scheduling</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/krifs/ticket_add">New ticket</a>
	<a href="/krifs/intervention_add">New intervention report</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/krifs_metrics/metrics">Krifs metrics</a>
	<a href="/krifs/report_krifs_outstanding_tickets">Tickets reports</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/krifs/manage_escalation_recipients">Escalation recipients</a>
</div>


<div id="menu_users_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 240px; display: none;">
	<a href="/user/manage_users">Users</a>
	<a href="/user/manage_removed_users">Removed Users</a>
	<a href="/user/manage_groups">Groups</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/user/manage_notification_recipients">Generic notifications recipients</a>
	<a href="/user/manage_customer_recipients">Notifications recipients - Keysource</a>
	<a href="/user/manage_customer_recipients_customers">Notifications recipients - customers</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/user/account_managers">Keysource account managers</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/user/manage_acl_roles">ACL roles</a>
	<a href="/user/manage_acl_categories">ACL categories</a>
	<a href="/user/manage_acl_items">ACL items</a>
</div>
<div id="menu_customers_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none; width:240px;">
	<a href="/customer/manage_customers">Customers</a>
	<a href="/customer/manage_customers_contacts">Customers contacts</a>
	<a href="/customer/manage_customers_comments">Customers comments</a>
	<a href="/customer/manage_customers_photos">Customers photos</a>
	<a href="/customer/manage_locations">Customers locations</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/customer/manage_cc_recipients">Tickets CC recipients</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/customer/customer_report">Customer report</a>
	<a href="/customer/manage_notifications_logs">Notifications logs</a>
	<a href="/customer/manage_messages_logs">Messages logs</a>
</div>

<div id="menu_config_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none; width:240px;">
	<a href="" onclick="return false;" style="font-weight:bold;">[ KAWACS ]</a>
	<a href="/kawacs/manage_alerts">Monitor Alerts</a>
	<a href="/kawacs/manage_monitor_items">Monitor Items - Computers</a>
	<a href="/kawacs/manage_monitor_items_peripherals">Monitor Items - Peripherals</a>
	<a href="/kawacs/manage_roles">Computers Roles</a>
	<a href="/kawacs/manage_peripherals_classes">Peripherals Classes</a>
	<a href="/kawacs/manage_kawacs_updates" id="menu_config_agent" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">Kawacs Agent &#0187;</a>
	<div id="menu_config_agent_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:240px;">
		<a href="/kawacs/manage_kawacs_updates">Kawacs Agent Updates</a>
		<a href="/kawacs/manage_kawacs_linux_updates">Kawacs Linux Updates</a>
		<a href="/kawacs/computers_agent_versions">Agent Versions</a>
		<a href="/kawacs/computers_linux_agent_versions">Linux Agent Versions</a>
	</div>
	<a href="/snmp/manage_mibs">MIBs Management</a>
	<a href="/discovery/manage_snmp_sysobjids">SNMP System Objects IDs</a>

    <div class="menusepar">&nbsp;</div>
    <a href="/keyos_connect">KeyOS Connect</a>

	<div class="menusepar">&nbsp;</div>
	<a href="" onclick="return false;" style="font-weight:bold;">[ KRIFS ]</a>
	<a href="/krifs/manage_escalation_recipients">Escalation recipients</a>
	
	<a href="" id="menu_config_configure" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">Configure &#0187;</a>
	<div id="menu_config_configure_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:240px;">
		<a href="/krifs/manage_statuses">Configure: Ticket statuses</a>
		<a href="/krifs/manage_types">Configure: Ticket types</a>
		<a href="/krifs/manage_action_types">Configure: Action types</a>
		<a href="/krifs/manage_activities">Configure: Activities</a>
		<a href="/krifs/manage_activities_categories">Configure: Activities Categories</a>
		<a href="/krifs/manage_intervention_locations">Configure: Intervention locations</a>
                <a href="/krifs/manage_support_emails">Configure: Support emails</a>
	</div>
	
	<a href="" id="menu_config_erpsync" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">ERP Synchronization &#0187;</a>
	<div id="menu_config_erpsync_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:300px;">
		<a href="/erp/erp_sync_customers">ERP synchronization: Customers</a>
		<a href="/erp/erp_sync_actypes">ERP synchronization: Action Types</a>
		<a href="/erp/erp_sync_actypes_categories">ERP synchronization: Action Types Categories</a>
		<a href="/erp/erp_sync_activities">ERP synchronization: Activities</a>
		<a href="/erp/erp_sync_engineers">ERP synchronization: Engineers</a>
	</div>

</div>
</div>

<!-- Workaround for the IE problem of displaying DIVs over SELECTs -->
<iframe
  id="DivShim"
  src="JavaScript: ''"
  scrolling="no"
  frameborder="0"
  style="position:absolute; top:0px; left:0px; display:none; height: 1px; width: 1px; background-color: white; color: white;">
</iframe>
<div id="menuHolder" class="menu"></div>
<script language="JavaScript" type="text/javascript">
	var IS_IE = false
	if (window.createPopup) IS_IE = true;
	
	//now we should change the appearance	
</script>

<table class="topheader">
	<tr>
		<td>
			<img src="/images/spacer.gif" width="670" height="1"><br>
			<table>
				{if $current_user->type == $smarty.const.USER_TYPE_KEYSOURCE}
				
				<tr id="mmenu_row">
					<td class="menu_separ_r"></td>
					<td class="menu_top_item" style="width:40px;"
						><a href="/home">Home</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kawacs" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kawacs">KAWACS</a></td>
					
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kerm" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kerm">KERM</a></td>
                                        
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_krifs" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/krifs">KRIFS</a></td>
                                        
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kalm" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kalm">KALM</a></td>
					
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_klara" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/klara">KLARA</a></td>
					
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_users" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/user">Users</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_customer" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/customer">Customers</a></td>
						
					<td class="menu_separ" id="mm_top_last">&nbsp;</td>
					<td id="menu_config" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="#" onclick="return false();">Config</a></td>
						
					<td class="menu_separ_l"></td>
					
				</tr>
				{elseif $current_user->type == $smarty.const.USER_TYPE_CUSTOMER and $current_user->administrator}
				<tr>
					<td class="menu_separ_r"></td>
					<td class="menu_top_item" style="width:40px;"
						><a href="/home">Home</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kawacs_ca" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kawacs">KAWACS</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kerm" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kerm">KERM</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_krifs_ca" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/krifs">KRIFS</a></td>
						
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_kalm" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/kalm">KALM</a></td>
					
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_klara" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/klara">KLARA</a></td>
					<td class="menu_separ">&nbsp;</td>
					<td id="menu_customers_ca" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"
						><a href="/customer">Customers</a></td>
					
					<td class="menu_separ_l"></td>
					
				</tr>
				{else}
				<tr>
					<td class="menu_separ_r"></td>
					<td class="menu_top_item"><a href="/home">Home</a></td>
					{*
                        <td class="menu_separ">&nbsp;</td>
                        <td id="menu_customer_krifs" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item">
                            <a href=".//customer_krifs">Tech support</a>
                        </td>
                    *}
					<td class="menu_separ" id="mmc_top_last">&nbsp;</td>
					<td class="menu_top_item"><a href="{$base_url}">Main site &gt;&gt;</a></td>
					<td class="menu_separ_l"></td>
				</tr>
				{/if}
			</table>
		</td>
		<td align="right"><div class="logo" /></td>
	</tr>
</table>
<p>
   

<table class="contentholder">
	<tr>
		{include file="_static_pages/left_menu.html"}
		{*
		if $left_menu == ''
			include file="_static_pages/left_menu.html"
		elseif $left_menu != 'none'
			include file="$left_menu"
		/if
		*}
		<td class="maincont" width="100%" >
		
