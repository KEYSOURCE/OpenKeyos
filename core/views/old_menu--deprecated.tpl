<div id="plugins_menu_container">
<div id="menu_kawacs_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;">
    <a href="/?cl=kawacs&amp;op=manage_computers">Computers</a>
    <a href="/?cl=kawacs&amp;op=kawacs_console">KAWACS Console</a>
    <a href="/?cl=kawacs&amp;op=customers_computer_count">Customer computers</a>

    <!-- added by victor -->
    <a href="/?cl=kawacs&amp;op=kawacs_backup_dashboard" id="menu_dashboards" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');">KAWACS Dashboards &#0187;</a>
    <div id="menu_dashboards_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');" style="display: none; width:240px;">
        <a href="/?cl=kawacs&amp;op=kawacs_backup_dashboard">Backup statuses dashboard</a>
        <a href="/?cl=kawacs&amp;op=kawacs_antivirus_dashboard">Antivirus statuses dashboard</a>
        <a href="/?cl=kawacs&amp;op=kawacs_inventory_dashboard">Inventory dashboard</a>
    </div>
    <!-- end added by victor -->

    <a href="/?cl=kawacs&amp;op=manage_notifications">Notifications Status</a>
    <a href="/?cl=kawacs&op=check_rbl_listed_servers">RBL Statuses</a>
    <a href="/?cl=kawacs&amp;op=computer_add">Add Computer Manually</a>
    <a href="/?cl=kawacs&amp;op=manage_peripherals">Peripherals</a>
    <a href="/?cl=kawacs&amp;op=manage_blackouts">Blackouts &amp; Ignored Computers</a>
    <a href="/?cl=warranties&amp;op=manage_warranties">Warranties</a>
    <a href="/?cl=warranties&amp;op=warranties_eow">Warranties - Out of Warranty</a>
    <a href="/?cl=warranties&amp;op=online_warranty_check">Check warranties online</a>

    <a href="/?cl=kawacs_removed&amp;op=manage_computers" id="menu_removed_devices_ca" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');">Removed Devices &#0187;</a>
    <div id="menu_removed_devices_ca_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_kawacs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs_ca');" style="display: none; width:240px;">
        <a href="/?cl=kawacs_removed&amp;op=manage_computers">Removed Computers</a>
        <a href="/?cl=kawacs_removed&amp;op=manage_peripherals">Removed Peripherals</a>
        <a href="/?cl=kawacs_removed&amp;op=manage_ad_printers">Removed AD Printers</a>
    </div>
</div>



<div id="menu_kawacs_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;">
    <a href="/?cl=kawacs&amp;op=manage_computers">Computers</a>
    <a href="/?cl=kawacs&amp;op=kawacs_console">KAWACS Console</a>
    <a href="/?cl=kawacs&amp;op=customers_computer_count">Customer computers</a>

    <!-- added by victor -->
    <a href="/?cl=kawacs&amp;op=kawacs_backup_dashboard" id="menu_dashboards" onMouseOver="showSubMenu(this.id, 'menu_kawacs');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs');">KAWACS Dashboards &#0187;</a>
    <div id="menu_dashboards_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_kawacs');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs');" style="display: none; width:240px;">
        <a href="/?cl=kawacs&amp;op=kawacs_backup_dashboard">Backup statuses dashboard</a>
        <a href="/?cl=kawacs&amp;op=kawacs_antivirus_dashboard">Antivirus statuses dashboard</a>
        <a href="/?cl=kawacs&amp;op=kawacs_inventory_dashboard">Inventory dashboard</a>
    </div>
    <!-- end added by victor -->

    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kawacs&op=create_kawacs_agent_deployer">Generate KawacsAgent deploy script</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kawacs&amp;op=manage_notifications">Notifications Status</a>
    <a href="/?cl=kawacs&op=check_rbl_listed_servers">RBL Statuses</a>
    <a href="/?cl=kawacs&op=manage_mremote_connections">Generate mremote file</a>
    <a href="/?cl=kawacs&amp;op=computer_add">Add Computer Manually</a>
    <a href="/?cl=kawacs&amp;op=manage_peripherals">Peripherals</a>
    <a href="/?cl=kawacs&amp;op=manage_blackouts">Blackouts &amp; Ignored Computers</a>
    <a href="/?cl=warranties&amp;op=manage_warranties">Warranties</a>
    <a id='menu_warranties_eow' href="/?cl=warranties&amp;op=warranties_eow">Warranties - Out of Warranty</a>
    <!-- <a href="/?cl=warranties&amp;op=online_warranty_check">Check warranties online</a> -->

    <a href="/?cl=kawacs_removed&amp;op=manage_computers" id="menu_removed_devices" onMouseOver="showSubMenu(this.id, 'menu_kawacs');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs');">Removed Devices &#0187;</a>
    <div id="menu_removed_devices_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_kawacs');" onMouseOut="hideSubMenu(this.id, 'menu_kawacs');" style="display: none; width:240px;">
        <a href="/?cl=kawacs_removed&amp;op=manage_computers">Removed Computers</a>
        <a href="/?cl=kawacs_removed&amp;op=manage_peripherals">Removed Peripherals</a>
        <a href="/?cl=kawacs_removed&amp;op=manage_ad_printers">Removed AD Printers</a>
        <div class="menusepar">&nbsp;</div>
        <a href="/?cl=kawacs_removed&amp;op=customers_inactive_computers">Inactive Customers with Computers</a>
        <a href="/?cl=kawacs_removed&amp;op=customers_inactive_peripherals">Inactive Customers with Peripherals</a>
    </div>

    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=discovery&amp;op=manage_discoveries">Networks Discoveries</a>
    <a href="/?cl=discovery&amp;op=manage_discoveries_settings">Networks Discoveries Settings</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kawacs&amp;op=manage_monitored_ips">Internet Monitoring</a>
    <a href="/?cl=kawacs&amp;op=manage_profiles">Monitor Profiles - Computers</a>
    <a href="/?cl=kawacs&amp;op=manage_profiles_periph">Monitor Profiles - Peripherals</a>
    <a href="/?cl=snmp&amp;op=snmp_devices">SNMP Monitored Devices</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kawacs&amp;op=reporting_issues">Computers Reporting Issues</a>
    <a href="/?cl=kawacs&amp;op=customers_allowed_ips">Customers Allowed IPs</a>
    <a href="/?cl=kawacs&amp;op=valid_dup_names">Valid Duplicate Names</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kawacs&amp;op=manage_quick_contacts">Computer Quick Contacts</a>
    <a href="/?cl=kawacs&amp;op=manage_oldest_contacts">Oldest Contacts</a>
</div>

<div id="menu_kerm_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none;">
    <a href="/?cl=kerm&amp;op=manage_ad_computers">AD Computers</a>
    <a href="/?cl=kerm&amp;op=manage_ad_users">AD Users &amp; Groups</a>
    <a href="/?cl=kerm&amp;op=manage_ad_printers">AD Printers</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kerm&amp;op=logon_computers">Logon Computers</a>
    {*<div class="menusepar">&nbsp;</div>
    <a href="/?cl=kerm&amp;op=customer_added_users">Manage users creation requests</a>*}
</div>

<div id="menu_krifs_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 240px; display: none; overflow:show;">
    <a href="/?cl=krifs&amp;op=manage_tickets">Tickets</a>
    <a href="/?cl=krifs&amp;op=tickets_stats" id="menu_tickets_stats" onMouseOver="showSubMenu(this.id, 'menu_krifs');" onMouseOut="hideSubMenu(this.id, 'menu_krifs');">Tickets Stats &#0187;</a>
    <div id="menu_tickets_stats_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_krifs');" onMouseOut="hideSubMenu(this.id, 'menu_krifs');" style="display: none; width:240px;">
        <a href="/?cl=krifs&amp;op=tickets_stats">Activity Stats</a>
        <a href="/?cl=krifs&amp;op=work_time_stats">Work Time Stats</a>
    </div>
    <!--
     ///Commented by victor
    <a href="/?cl=krifs&amp;op=manage_interventions">Intervention Reports</a>
    -->
    <!-- added by Victor -->
    <a href="/?cl=krifs&amp;op=manage_interventions" id="menu_IR" onMouseOver="showSubMenu(this.id, 'menu_krifs');" onMouseOut="hideSubMenu(this.id, 'menu_krifs');">Intervention Reports &#0187;</a>
    <div id="menu_IR_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_krifs');" onMouseOut="hideSubMenu(this.id, 'menu_krifs');" style="display: none; width:240px;">
        <a href="/?cl=krifs&amp;op=manage_interventions">Intervention Reports</a>
        <a href="/?cl=krifs&amp;op=interventions_print_console">IR print console</a>
        <a href="/?cl=krifs&amp;op=intervention_approval_console">IR approval console</a>
    </div>
    <!-- end added by victor -->
    <a href="/?cl=krifs&amp;op=manage_timesheets">Timesheets</a>
    <a href="/?cl=krifs&amp;op=manage_timesheets_extended">Timesheet reports</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=erp&amp;op=manage_customer_orders">Customer Orders / Subscriptions</a>
    <a href="/?cl=erp&amp;op=manage_interventions_exports">Interventions Exports</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=manage_tasks">Tasks Scheduling</a>
    <a href="/?cl=krifs&amp;op=tbs_tickets">TBS Tickets</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=ticket_add">New ticket</a>
    <a href="/?cl=krifs&amp;op=intervention_add">New intervention report</a>
    <a href="/?cl=krifs&amp;op=tickets_from_emails">Tickets from support emails</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=manage_saved_searches">Saved searches</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs_metrics&amp;op=metrics">Krifs metrics</a>
    <a href="/?cl=krifs_metrics&amp;op=metrics_compare">Krifs comparative metrics</a>
    <a href="/?cl=krifs&amp;op=report_krifs_outstanding_tickets">Tickets reports</a>
    <a href="/?cl=krifs&amp;op=now_working">Who is doing what</a>

</div>

<div id="menu_krifs_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 240px; display: none; overflow:show;">
    <a href="/?cl=krifs&amp;op=manage_tickets">Tickets</a>
    <!-- added -->
    <a href="/?cl=krifs&amp;op=manage_interventions" id="menu_IR_ca" onMouseOver="showSubMenu(this.id, 'menu_krifs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_krifs_ca');">Intervention Reports &#0187</a>
    <div id="menu_IR_ca_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_krifs_ca');" onMouseOut="hideSubMenu(this.id, 'menu_krifs_ca');" style="display: none; width:240px;">
        <a href="/?cl=krifs&amp;op=manage_interventions">Intervention Reports</a>
        <a href="/?cl=krifs&amp;op=interventions_print_console">IR print console</a>
    </div>
    <!-- end added -->
    <a href="/?cl=krifs&amp;op=manage_timesheets">Timesheets</a>
    <a href="/?cl=erp&amp;op=manage_customer_orders">Customer Orders / Subscriptions</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=manage_tasks">Tasks Scheduling</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=ticket_add">New ticket</a>
    <a href="/?cl=krifs&amp;op=intervention_add">New intervention report</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs_metrics&amp;op=metrics">Krifs metrics</a>
    <a href="/?cl=krifs&amp;op=report_krifs_outstanding_tickets">Tickets reports</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=krifs&amp;op=manage_escalation_recipients">Escalation recipients</a>
</div>


<div id="menu_kalm_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none;">
    <a href="/?cl=kalm&amp;op=manage_licenses">Customer licenses</a>
    <a href="/?cl=kalm&amp;op=exceeded_licenses">Exceeded licenses</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=kalm&amp;op=manage_software">Software packages</a>
    <a href="/?cl=kalm&amp;op=software_add">Add software package</a>
</div>

<div id="menu_klara_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none;">
    <a href="/?cl=klara&amp;op=manage_access">Access information</a>
    <a href="/?cl=klara&amp;op=manage_access_phones">Access phones</a>
    <a href="/?cl=klara&amp;op=manage_customer_internet_contracts">Customer Internet contracts</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=klara&amp;op=manage_providers">Manage Internet Providers</a>
</div>

<!-- XXX added by Victor
<div id="menu_kams_div" class="menu" onmouseover="showMenu(this.id);" onmouseout="hideMenu(this.id);" style="display: none;">
	<a href="/?cl=kams&amp;op=manage_assets">Assets</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/?cl=kams&amp;op=manage_contracts">Contracts</a>
	<a href="/?cl=kams&amp;op=manage_contract_types">Contracts types</a>
	<div class="menusepar">&nbsp;</div>
	<a href="/?cl=kams&amp;op=syncronize" >Syncronize</a>
</div>
XXX end added by Victor -->

<div id="menu_users_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 240px; display: none;">
    <a href="/?cl=user&amp;op=manage_users">Users</a>
    <a href="/?cl=user&amp;op=manage_removed_users">Removed Users</a>
    <a href="/?cl=user&amp;op=manage_groups">Groups</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=user&amp;op=manage_notification_recipients">Generic notifications recipients</a>
    <a href="/?cl=user&amp;op=manage_customer_recipients">Notifications recipients - Keysource</a>
    <a href="/?cl=user&amp;op=manage_customer_recipients_customers">Notifications recipients - customers</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=user&amp;op=account_managers">Keysource account managers</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=user&amp;op=manage_acl_roles">ACL roles</a>
    <a href="/?cl=user&amp;op=manage_acl_categories">ACL categories</a>
    <a href="/?cl=user&amp;op=manage_acl_items">ACL items</a>
</div>

<div id="menu_customers_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none; width:240px;">
    <a href="/?cl=customer&amp;op=manage_customers">Customers</a>
    <a href="/?cl=customer&amp;op=customers_suspended_alerts">Customers with suspended alerts</a>
    <a href="/?cl=customer&amp;op=manage_customers_contacts">Customers contacts</a>
    <a href="/?cl=customer&amp;op=manage_customers_comments">Customers comments</a>
    <a href="/?cl=customer&amp;op=manage_customers_photos">Customers photos</a>
    <a href="/?cl=customer&amp;op=manage_locations">Customers locations</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=customer&amp;op=manage_cc_recipients">Tickets CC recipients</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=customer&amp;op=manage_suppliers">Suppliers</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=customer&amp;op=customer_report">Customer report</a>
    <a href="/?cl=customer&amp;op=manage_notifications_logs">Notifications logs</a>
    <a href="/?cl=customer&amp;op=manage_messages_logs">Messages logs</a>
</div>

<div id="menu_customers_ca_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none; width:240px;">
    <a href="/?cl=customer&amp;op=manage_customers">Customers</a>
    <a href="/?cl=customer&amp;op=manage_customers_contacts">Customers contacts</a>
    <a href="/?cl=customer&amp;op=manage_customers_comments">Customers comments</a>
    <a href="/?cl=customer&amp;op=manage_customers_photos">Customers photos</a>
    <a href="/?cl=customer&amp;op=manage_locations">Customers locations</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=customer&amp;op=manage_cc_recipients">Tickets CC recipients</a>
    <div class="menusepar">&nbsp;</div>
    <a href="/?cl=customer&amp;op=customer_report">Customer report</a>
    <a href="/?cl=customer&amp;op=manage_notifications_logs">Notifications logs</a>
    <a href="/?cl=customer&amp;op=manage_messages_logs">Messages logs</a>
</div>

<div id="menu_config_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="display: none; width:240px;">
    <a href="" onclick="return false;" style="font-weight:bold;">[ KAWACS ]</a>
    <a href="/?cl=kawacs&amp;op=manage_alerts">Monitor Alerts</a>
    <a href="/?cl=kawacs&amp;op=manage_monitor_items">Monitor Items - Computers</a>
    <a href="/?cl=kawacs&amp;op=manage_monitor_items_peripherals">Monitor Items - Peripherals</a>
    <a href="/?cl=kawacs&amp;op=manage_roles">Computers Roles</a>
    <a href="/?cl=kawacs&amp;op=manage_peripherals_classes">Peripherals Classes</a>
    <a href="/?cl=kawacs&amp;op=manage_kawacs_updates" id="menu_config_agent" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">Kawacs Agent &#0187;</a>
    <div id="menu_config_agent_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:240px;">
        <a href="/?cl=kawacs&amp;op=manage_kawacs_updates">Kawacs Agent Updates</a>
        <a href="/?cl=kawacs&amp;op=manage_kawacs_linux_updates">Kawacs Linux Updates</a>
        <a href="/?cl=kawacs&amp;op=computers_agent_versions">Agent Versions</a>
        <a href="/?cl=kawacs&amp;op=computers_linux_agent_versions">Linux Agent Versions</a>
    </div>
    <a href="/?cl=snmp&amp;op=manage_mibs">MIBs Management</a>
    <a href="/?cl=discovery&amp;op=manage_snmp_sysobjids">SNMP System Objects IDs</a>

    <div class="menusepar">&nbsp;</div>
    <a href="" onclick="return false;" style="font-weight:bold;">[ KRIFS ]</a>
    <a href="/?cl=krifs&amp;op=manage_escalation_recipients">Escalation recipients</a>

    <a href="" id="menu_config_configure" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">Configure &#0187;</a>
    <div id="menu_config_configure_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:240px;">
        <a href="/?cl=krifs&amp;op=manage_statuses">Configure: Ticket statuses</a>
        <a href="/?cl=krifs&amp;op=manage_types">Configure: Ticket types</a>
        <a href="/?cl=krifs&amp;op=manage_action_types">Configure: Action types</a>
        <a href="/?cl=krifs&amp;op=manage_activities">Configure: Activities</a>
        <a href="/?cl=krifs&amp;op=manage_activities_categories">Configure: Activities Categories</a>
        <a href="/?cl=krifs&amp;op=manage_intervention_locations">Configure: Intervention locations</a>
        <a href="/?cl=krifs&amp;op=manage_support_emails">Configure: Support emails</a>
    </div>

    <a href="" id="menu_config_erpsync" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');">ERP Synchronization &#0187;</a>
    <div id="menu_config_erpsync_div" class="menu" onMouseOver="showSubMenu(this.id, 'menu_config');" onMouseOut="hideSubMenu(this.id, 'menu_config');" style="display: none; width:300px;">
        <a href="/?cl=erp&amp;op=erp_sync_customers">ERP synchronization: Customers</a>
        <a href="/?cl=erp&amp;op=erp_sync_actypes">ERP synchronization: Action Types</a>
        <a href="/?cl=erp&amp;op=erp_sync_actypes_categories">ERP synchronization: Action Types Categories</a>
        <a href="/?cl=erp&amp;op=erp_sync_activities">ERP synchronization: Activities</a>
        <a href="/?cl=erp&amp;op=erp_sync_engineers">ERP synchronization: Engineers</a>
    </div>
</div>
</div>