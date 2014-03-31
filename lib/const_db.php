<?php

/**
* Database specific constants
* 
* @package
* @subpackage Constants_database
*
*/

/** Table with users information */
define ('TBL_USERS', 'users');
/** Table with groups membership information */
define ('TBL_USERS_GROUPS', 'users_groups');
/** Table with user phone numbers */
define ('TBL_USERS_PHONES', 'users_phones');
/** Table with the lists of customers assigned to users */
define ('TBL_USERS_CUSTOMERS_ASSIGNED', 'users_customers_assigned');
/** Table with the lists of favorite customers for the users */
define ('TBL_USERS_CUSTOMERS_FAVORITES', 'users_customers_favorites');
/** Table storing Exchange connection information for a user */
define ('TBL_USERS_EXCHANGE', 'users_exchange');

/** Table with monitoring items definitions */
define ('TBL_MONITOR_ITEMS', 'monitor_items');

/** Table with monitoring profiles */
define ('TBL_MONITOR_PROFILES', 'monitor_profiles');
/** Table with monitoring profiles items */
define ('TBL_MONITOR_PROFILES_ITEMS', 'monitor_profiles_items');

/** Table with peripherals monitoring profiles */
define ('TBL_MONITOR_PROFILES_PERIPH', 'monitor_profiles_periph');
/** Table with monitoring profiles items */
define ('TBL_MONITOR_PROFILES_ITEMS_PERIPH', 'monitor_profiles_items_periph');

/** Table with computer data */
define ('TBL_COMPUTERS', 'computers');
/** Table with values stored for monitoring computer items */
define ('TBL_COMPUTERS_ITEMS', 'computers_items');
/** Table with the logged values stored for monitoring computer items */
define ('TBL_COMPUTERS_ITEMS_LOG', 'computers_items_log');
/** Table storing the file versions for various computers */
define ('TBL_COMPUTERS_AGENT_VERSIONS', 'computers_agent_versions');
/** Table storing the file versions for various Linux computers */
define ('TBL_COMPUTERS_AGENT_LINUX_VERSIONS', 'computers_agent_linux_versions');
/** Table storing the markings when computers data is being updated */
define ('TBL_COMPUTERS_UPDATING', 'computers_updating');
/** Table storing the list of computers that are in blackouts */
define ('TBL_COMPUTERS_BLACKOUTS', 'computers_blackouts');
/** Table with computer notes */
define ('TBL_COMPUTERS_NOTES', 'computers_notes');
/** Table with monitored IPs */
define ('TBL_MONITORED_IPS', 'monitored_ips');

/** Table with removed computers data */
define ('TBL_REMOVED_COMPUTERS', 'removed_computers');
/** Table with valued stored for monitoring items for removed computers */
define ('TBL_REMOVED_COMPUTERS_ITEMS', 'removed_computers_items');
/** Table with notes for removed computers */
define ('TBL_REMOVED_COMPUTERS_NOTES', 'removed_computers_notes');


/** Table storing the remote IPs from which Kawacs Agent is allowed to report for each customer */
define ('TBL_CUSTOMERS_ALLOWED_IPS', 'customers_allowed_ips');
/** Table storing the allowed duplicate names for the computers */
define ('TBL_VALID_DUP_NAMES', 'valid_dup_names');

/** Table storing the roles definitions */
define ('TBL_ROLES', 'roles');
/** Table storing the roles assigned to various computers */
define ('TBL_COMPUTERS_ROLES', 'computers_roles');

/** Table storing the network discoveries settings for each customer */
define ('TBL_DISCOVERIES_SETTINGS', 'discoveries_settings');
/** Table storing the network discoveries settings details for customers */
define ('TBL_DISCOVERIES_SETTINGS_DETAILS', 'discoveries_settings_details');
/** Table storing the discovered devices */
define ('TBL_DISCOVERIES', 'discoveries');
/** Table storing the list of SNMP system object ids (devices types) found during networks discoveries */
define ('TBL_SNMP_SYSOBJIDS', 'snmp_sysobjids');

/** Table with classes of peripherals */
define ('TBL_PERIPHERALS_CLASSES', 'peripherals_classes');
/** Table with field definitions for peripherals classes */
define ('TBL_PERIPHERALS_CLASSES_FIELDS', 'peripherals_classes_fields');
/** Table storing the mappings between peripheral classes fields and the monitoring items from the associated profile */
define ('TBL_PERIPHERALS_CLASSES_PROFILES_FIELDS', 'peripherals_classes_profiles_fields');

/** Table storing customer peripherals */
define ('TBL_PERIPHERALS', 'peripherals');
/** Table storing customer peripherals field values */
define ('TBL_PERIPHERALS_FIELDS', 'peripherals_fields');
/** Table storing computers to which the peripherals are linked */
define ('TBL_PERIPHERALS_COMPUTERS', 'peripherals_computers');
/** Table storing the warranty information for AD printers */
define ('TBL_AD_PRINTERS_WARRANTIES', 'ad_printers_warranties');
/** Table storing the extra infos for AD Printers, such as assets numbers and SNMP monitoring info */
define ('TBL_AD_PRINTERS_EXTRAS', 'ad_printers_extras');
/** Table storing the removed AD printers information */
define ('TBL_REMOVED_AD_PRINTERS', 'removed_ad_printers');

/** Table storing the removed peripherals */
define ('TBL_REMOVED_PERIPHERALS', 'removed_peripherals');
/** Table storing the field values for removed peripherals */
define ('TBL_REMOVED_PERIPHERALS_FIELDS', 'removed_peripherals_fields');

/** Table with values stored for monitoring peripherals items (including AD Printers) */
define ('TBL_PERIPHERALS_ITEMS', 'peripherals_items');
/** Table with the logged values stored for monitoring peripherals items (including AD Printers) */
define ('TBL_PERIPHERALS_ITEMS_LOG', 'peripherals_items_log');
/** Table with values stored for monitoring items for removed peripherals and AD Printers */
define ('TBL_REMOVED_PERIPHERALS_ITEMS', 'removed_peripherals_items');

/** Table with customers data */
define ('TBL_CUSTOMERS', 'customers');
/** Table with customers contacts data */
define ('TBL_CUSTOMERS_CONTACTS', 'customers_contacts');
/** Table with customers contacts phones data */
define ('TBL_CUSTOMERS_CONTACTS_PHONES', 'customers_contacts_phones');
/** Table with customer details (text comments) */
define ('TBL_CUSTOMERS_COMMENTS', 'customers_comments');
/** Table with customer photos */
define ('TBL_CUSTOMERS_PHOTOS', 'customers_photos');
/** Table with the default CC recipients for customers tickets */
define ('TBL_CUSTOMERS_CC_RECIPIENTS', 'customers_cc_recipients');

/** Table storing notifications raised in the system */
define ('TBL_NOTIFICATIONS', 'notifications');
/** Table storing the recipients of the the notifications raised in the system */
define ('TBL_NOTIFICATIONS_RECIPIENTS', 'notifications_recipients');
/** Table storing the general recipients for different types of notifications */
define ('TBL_NOTIFICATIONS_GENERAL_RECIPIENTS', 'notifications_general_recipients');
/** Table storing the Keysource notification recipients assigned to specific customers */
define ('TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS', 'notifications_customers_recipients');
/** Table storing the customer notification recipients assigned to specific customers */
define ('TBL_NOTIFICATIONS_CUSTOMERS_RECIPIENTS_CUSTOMERS', 'notifications_customers_recipients_customers');

/** Prefix for tables storing the logs of e-mail messages (notification) sent to customer users */
define ('TBL_MESSAGES_LOG', 'messages_log');

/** Table storing definitions of KAWACS alerts */
define ('TBL_ALERTS', 'alerts');
/** Table storing the criterias for KAWACS alerts */
define ('TBL_ALERTS_CONDITIONS', 'alerts_conditions');
/** Table storing specific recipients for notifications - if any*/
define ('TBL_ALERTS_RECIPIENTS', 'alerts_recipients');
/** Table storing the relations between monitor profiles and alerts */
define ('TBL_PROFILES_ALERTS', 'profiles_alerts');
/** Table storing the relations between peripherals monitor profiles and alerts */
define ('TBL_PROFILES_PERIPH_ALERTS', 'profiles_periph_alerts');
/** Table storing the IDs of the fields who's values to include in notifications subjects */
define ('TBL_ALERTS_SEND_FIELDS', 'alerts_send_fields');
/** Table storing the values to include in criterias set for items of type list */
define ('TBL_ALERTS_LISTS_VALUES', 'alerts_lists_values');


/** Table for storing data about the Kawacs Agent releases */
define ('TBL_KAWACS_AGENT_UPDATES', 'kawacs_agent_updates');
/** Table for storing data about the Kawacs Agent releases files */
define ('TBL_KAWACS_AGENT_UPDATES_FILES', 'kawacs_agent_updates_files');
/** Table for storing data about the Kawacs Agent Linux releases */
define ('TBL_KAWACS_AGENT_LINUX_UPDATES', 'kawacs_agent_linux_updates');
/** Table for storing the list of computers which should get pre-release updates of KawacsAgent */
define ('TBL_KAWACS_AGENT_UPDATES_PREVIEWS', 'kawacs_agent_updates_previews');

/** Table for storing quick reports received from Kawacs Manager */
define ('TBL_COMPUTER_QUICK_CONTACTS', 'computer_quick_contacts');

/** Table storing remote access phone numbers */
define ('TBL_ACCESS_PHONES', 'access_phones');
/** Table storing remote (public) IP address information for customers */
define ('TBL_REMOTE_ACCESS', 'remote_access');
/** Table storing information about remotes service accessible for customers computers */
define ('TBL_COMPUTERS_REMOTE_SERVICES', 'computers_remote_services');
/** Table storing computers passwords */
define ('TBL_COMPUTERS_PASSWORDS', 'computers_passwords');

/** Table storing user/computers Plink settings */
define ('TBL_PLINK', 'plink');
/** Table storing user/computers Plink services list */
define ('TBL_PLINK_SERVICES', 'plink_services');
/** Table storing user/peripherals Plink settings */
define ('TBL_PERIPHERAL_PLINK', 'peripheral_plink');
/** Table storing user/peripherals Plink services list */
define ('TBL_PERIPHERAL_PLINK_SERVICES', 'peripheral_plink_services');

/** Table storing "pre_defined" locations - countries, provinces, cities */
define ('TBL_LOCATIONS_FIXED', 'locations_fixed');
/** Table storing locations from customers */
define ('TBL_LOCATIONS', 'locations');
/** Table storing comments for customer locations */
define ('TBL_LOCATIONS_COMMENTS', 'locations_comments');

/** Table storing information about service providers */
define ('TBL_PROVIDERS', 'providers');
/** Table storing information about service providers contracts */
define ('TBL_PROVIDERS_CONTRACTS', 'providers_contracts');
/** Table storing information about service providers contacts */
define ('TBL_PROVIDERS_CONTACTS', 'providers_contacts');
/** Table storing phone numbers for provider contacts */
define ('TBL_PROVIDERS_CONTACTS_PHONES', 'providers_contacts_phones');
/** Table storing contracts between customers and internet providers */
define ('TBL_CUSTOMERS_INTERNET_CONTRACTS', 'customers_internet_contracts');
/** Table storing attachments for contracts between customers and internet providers */
define ('TBL_CUSTOMERS_INTERNET_CONTRACTS_ATTACHMENTS', 'customers_internet_contracts_attachments');

/** Table storing information about suppliers */
define ('TBL_SUPPLIERS', 'suppliers');
/** Table storing information about suppliers service packages */
define ('TBL_SUPPLIERS_SERVICE_PACKAGES', 'suppliers_service_packages');
/** Table storing information about service levels */
define ('TBL_SERVICE_LEVELS', 'service_levels');

/** Table for storing information about software products */
define ('TBL_SOFTWARE', 'software');
/** Table for storing regexps for identifying licensing software from the KAWCS collected info */
define ('TBL_SOFTWARE_MATCHES', 'software_matches');
/** Table for storing information about the licenses purchased by a customer */
define ('TBL_SOFTWARE_LICENSES', 'software_licenses');
/** Table for storing serial numbers for software licenses */
define ('TBL_SOFTWARE_LICENSES_SN', 'software_licenses_sn');
/** Table for storing file attachments for software licenses */
define ('TBL_SOFTWARE_LICENSES_FILES', 'software_licenses_files');

/** Table for storing ACL permissions for users */
define ('TBL_ACL', 'acl');
/** Table for storing ACL item objects */
define ('TBL_ACL_ITEMS', 'acl_items');
/** Table for storing ACL categories */
define ('TBL_ACL_CATEGORIES', 'acl_categories');
/** Table for storing list of operations for ACL items */
define ('TBL_ACL_ITEMS_OPERATIONS', 'acl_items_operations');
/** Table for storing ACL profiles */
define ('TBL_ACL_ROLES', 'acl_roles');
/** Table for storing ACL profile items */
define ('TBL_ACL_ROLES_ITEMS', 'acl_roles_items');


/** Table storing the events sources for events logs */
define ('TBL_EVENTS_SOURCES', 'events_sources');
/** Table storing the definitions of what computers events log to request, either for profiles or for individual computers */
define ('TBL_EVENTS_LOG_REQUESTED', 'events_log_requested');

/** Table storing the MIBs loaded into the system */
define ('TBL_MIBS', 'mibs');
/** Table specifying all the files which belong to a given MIB */
define ('TBL_MIBS_FILES', 'mibs_files');
/** Table storing the OIDs details for the MIBs */
define ('TBL_MIBS_OIDS', 'mibs_oids');
/** Table storing the explanations of OIDs values */
define ('TBL_MIBS_OIDS_VALS', 'mibs_oids_vals');

//XXX modified by Victor
/** Table storing generic assets data */
define('TBL_ASSETS', 'assets');
/** Table storing the various types and classes of assets */
define('TBL_ASSET_CATEGORIES', 'asset_categories');
/** Table storing financial informations about assets */
define('TBL_ASSET_FINANCIAL_INFOS', 'asset_financial_infos');
/** Table storing different types of currency */
define('TBL_CURRENCY', 'currency');
/** Table storing contract types informations */
define('TBL_CONTRACT_TYPES', 'contract_types');
/** Table storing contracts data */
define('TBL_CONTRACTS', 'contracts');
/** Table storing payment periods for contracts */
define('TBL_CONTRACTS_PAYMENT_PERIODS', 'contracts_payment_periods');
/**
 * Table defining the links between contracts and assets
 * 1 asset can be the object of multiple contracts
 * 1 contract can have multiple assets assigned
 * many-to-many relation between TBL_ASSETS and TBL_CONTRACTS
 */
define('TBL_CONTRACTS_ASSETS', 'contracts_assets');
/** Table storing the customer accounts assigned for one user */
define('TBL_USERS_CUSTOMERS', 'users_customers');
/** Where we keep the archived accounts */
define('TBL_REMOVED_USERS', 'removed_users');
/** Where we keep the customer accounts for the removed user accounts */
define('TBL_REMOVED_USERS_CUSTOMERS', 'removed_users_customers');
define('TBL_USER_ACTION_LOG', 'user_action_log');

define('TBL_SUPPLIER_CUSTOMERS', 'supplier_customers');
define('TBL_SUPPLIERS_C_CUSTOMERS', 'suppliers_c_customers');

define('TBL_TICKETS_MANUAL_CC', 'tickets_manual_cc');

define('TBL_KERM_AD_GROUPS', 'kerm_ad_groups');
define('TBL_KERM_AD_USERS', 'kerm_ad_users');
define('TBL_KERM_AD_REPORTS', 'kerm_ad_reports');
define('TBL_KERM_CUSATOMERS_DOMAINS', 'kerm_customers_domains');

define('TBL_MREMOTE_CONNECTIONS', 'mremote_connections');
define('TBL_MREMOTE_CONNECTION_INFO', 'mremote_connection_info');

define('TBL_CUSTOMER_TEMPLATE_STYLE', 'customer_template_styles');

define('TBL_WORK_MARKERS', 'work_markers');

define('TBL_COMPUTER_GROUPS', 'computer_groups');
define('TBL_COMPUTER_GROUPS_COMPUTERS', 'computer_groups_computers');

/**
 *  Knowledgebase related tables
 * */
/** Table for storing KB categories */
define('TBL_KB_CATEGORIES', 'kb_categories');
/** Table for storing the KB articles */
define('TBL_KB_ARTICLES', 'kb_articles');
/** Table for storing the different part of the article */
define('TBL_KB_ARTICLES_SECTIONS', 'kb_articles_sections');
/** Table storing the attachments for KB articles */
define('TBL_KB_ARTICLES_ATTACHMENTS', 'kb_articles_attachments');

define('TBL_COMPUTER_STOLEN', 'computer_stolen');

/** Table for storing  customer nagvis account*/
define('TBL_CUSTOMER_NAGVIS_ACCOUNT', 'customer_nagvis_account');

/** Table for storig computer LogMeIn ID */
define('TBL_COMPUTER_LOGMEIN', 'computer_logmein');

/** Table for storing support email accounts from wich will be created ticket*/
define('TBL_IMAP_SETTINGS', 'imap_settings');

/* tables for storing the web-access in KLARA */
define('TBL_WEB_ACCESS', 'web_access');
define('TBL_WEB_ACCESS_RESOURCES', 'web_access_resources');
?>