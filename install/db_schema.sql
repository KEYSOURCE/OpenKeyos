-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (x86_64)
--
-- Host: 192.168.0.10    Database: keyos
-- ------------------------------------------------------
-- Server version	5.1.67-0ubuntu0.11.10.1












--
-- Table structure for table `access_phones`
--

DROP TABLE IF EXISTS `access_phones`;


CREATE TABLE `access_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `device_type` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `customer_id` (`customer_id`),
  KEY `phone` (`phone`),
  KEY `device_type` (`device_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl`
--

DROP TABLE IF EXISTS `acl`;


CREATE TABLE `acl` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_role_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`acl_role_id`),
  KEY `acl_role_id` (`acl_role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl_categories`
--

DROP TABLE IF EXISTS `acl_categories`;


CREATE TABLE `acl_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl_items`
--

DROP TABLE IF EXISTS `acl_items`;


CREATE TABLE `acl_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `special` tinyint(1) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `special` (`special`),
  KEY `category_id` (`category_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl_items_operations`
--

DROP TABLE IF EXISTS `acl_items_operations`;


CREATE TABLE `acl_items_operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_item_id` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '',
  `function` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `acl_item_id` (`acl_item_id`),
  KEY `module` (`module`),
  KEY `function` (`function`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;


CREATE TABLE `acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `acl_roles_items`
--

DROP TABLE IF EXISTS `acl_roles_items`;


CREATE TABLE `acl_roles_items` (
  `acl_role_id` int(11) NOT NULL DEFAULT '0',
  `acl_item_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`acl_item_id`,`acl_role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `action_types`
--

DROP TABLE IF EXISTS `action_types`;


CREATE TABLE `action_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_id` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `category` tinyint(4) NOT NULL DEFAULT '0',
  `price_type` tinyint(4) NOT NULL DEFAULT '0',
  `contract_types` tinyint(4) NOT NULL DEFAULT '0',
  `billable` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `special_type` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `erp_name` varchar(255) DEFAULT NULL,
  `contract_sub_type` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `erp_code` varchar(50) DEFAULT NULL,
  `family` varchar(100) NOT NULL DEFAULT '',
  `helpdesk` int(11) NOT NULL DEFAULT '0',
  `billing_unit` int(11) NOT NULL DEFAULT '60',
  PRIMARY KEY (`id`),
  KEY `erp_id` (`erp_id`),
  KEY `name` (`name`),
  KEY `price_type` (`price_type`),
  KEY `contract_types` (`contract_types`),
  KEY `category` (`category`),
  KEY `billable` (`billable`),
  KEY `special_type` (`special_type`),
  KEY `user_id` (`user_id`),
  KEY `contract_sub_type` (`contract_sub_type`),
  KEY `active` (`active`),
  KEY `erp_code` (`erp_code`),
  KEY `family` (`family`),
  KEY `helpdesk` (`helpdesk`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `action_types_categories`
--

DROP TABLE IF EXISTS `action_types_categories`;


CREATE TABLE `action_types_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_id` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `erp_id` (`erp_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;


CREATE TABLE `activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `is_travel` tinyint(4) NOT NULL DEFAULT '0',
  `erp_id` varchar(25) NOT NULL DEFAULT '',
  `erp_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `category_id` (`category_id`),
  KEY `is_travel` (`is_travel`),
  KEY `erp_id` (`erp_id`),
  KEY `erp_name` (`erp_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `activities_categories`
--

DROP TABLE IF EXISTS `activities_categories`;


CREATE TABLE `activities_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `erp_code` varchar(30) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `activities_users`
--

DROP TABLE IF EXISTS `activities_users`;


CREATE TABLE `activities_users` (
  `activity_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `erp_activity_id` varchar(25) DEFAULT NULL,
  KEY `activity_id` (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `erp_activity_id` (`erp_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `ad_printers_extras`
--

DROP TABLE IF EXISTS `ad_printers_extras`;


CREATE TABLE `ad_printers_extras` (
  `canonical_name` varchar(200) NOT NULL DEFAULT '',
  `asset_number` varchar(10) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `snmp_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `snmp_computer_id` int(11) NOT NULL DEFAULT '0',
  `snmp_ip` varchar(50) NOT NULL DEFAULT '',
  `last_contact` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`canonical_name`),
  UNIQUE KEY `asset_number` (`asset_number`),
  UNIQUE KEY `id` (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `snmp_enabled` (`snmp_enabled`),
  KEY `snmp_computer_id` (`snmp_computer_id`),
  KEY `snmp_ip` (`snmp_ip`),
  KEY `last_contact` (`last_contact`),
  KEY `customer_id` (`customer_id`),
  KEY `date_created` (`date_created`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `ad_printers_locations`
--

DROP TABLE IF EXISTS `ad_printers_locations`;


CREATE TABLE `ad_printers_locations` (
  `canonical_name` varchar(200) NOT NULL DEFAULT '',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`canonical_name`),
  KEY `customer_id` (`customer_id`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `ad_printers_warranties`
--

DROP TABLE IF EXISTS `ad_printers_warranties`;


CREATE TABLE `ad_printers_warranties` (
  `canonical_name` varchar(200) NOT NULL DEFAULT '',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `sn` varchar(50) NOT NULL DEFAULT '',
  `warranty_starts` int(11) NOT NULL DEFAULT '0',
  `warranty_ends` int(11) NOT NULL DEFAULT '0',
  `service_package_id` int(11) NOT NULL DEFAULT '0',
  `service_level_id` int(11) NOT NULL DEFAULT '0',
  `contract_number` varchar(255) DEFAULT NULL,
  `hw_product_id` varchar(255) NOT NULL DEFAULT '',
  `product_number` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`canonical_name`),
  KEY `customer_id` (`customer_id`),
  KEY `sn` (`sn`),
  KEY `warranty_starts` (`warranty_starts`),
  KEY `warranty_ends` (`warranty_ends`),
  KEY `service_package_id` (`service_package_id`),
  KEY `service_level_id` (`service_level_id`),
  KEY `contract_number` (`contract_number`),
  KEY `hw_product_id` (`hw_product_id`),
  KEY `product_number` (`product_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;


CREATE TABLE `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT '0',
  `event_code` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `on_contact_only` tinyint(4) NOT NULL DEFAULT '0',
  `join_type` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_days` int(11) NOT NULL DEFAULT '0',
  `send_to` tinyint(4) NOT NULL DEFAULT '1',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `delay_email` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `item_id` (`item_id`),
  KEY `name` (`name`),
  KEY `on_contact_only` (`on_contact_only`),
  KEY `join_type` (`join_type`),
  KEY `ignore_days` (`ignore_days`),
  KEY `send_to` (`send_to`),
  KEY `delay_email` (`delay_email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `alerts_conditions`
--

DROP TABLE IF EXISTS `alerts_conditions`;


CREATE TABLE `alerts_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `criteria` int(11) NOT NULL DEFAULT '0',
  `value` tinytext NOT NULL,
  `value_type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `alert_id` (`alert_id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `alerts_lists_values`
--

DROP TABLE IF EXISTS `alerts_lists_values`;


CREATE TABLE `alerts_lists_values` (
  `alert_id` int(11) NOT NULL DEFAULT '0',
  `list_value` int(11) NOT NULL DEFAULT '0',
  KEY `alert_id` (`alert_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `alerts_recipients`
--

DROP TABLE IF EXISTS `alerts_recipients`;


CREATE TABLE `alerts_recipients` (
  `alert_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`alert_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `alerts_send_fields`
--

DROP TABLE IF EXISTS `alerts_send_fields`;


CREATE TABLE `alerts_send_fields` (
  `alert_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`alert_id`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `bk_notifications`
--

DROP TABLE IF EXISTS `bk_notifications`;


CREATE TABLE `bk_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_code` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `raised` int(11) NOT NULL DEFAULT '0',
  `raised_last` int(11) NOT NULL DEFAULT '0',
  `raised_count` int(11) NOT NULL DEFAULT '0',
  `object_class` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `object_event_code` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `emailed_last` int(11) NOT NULL DEFAULT '0',
  `suspend_email` int(11) NOT NULL DEFAULT '0',
  `template` varchar(255) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT '0',
  `no_repeat` tinyint(4) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `object_event_code` (`object_event_code`),
  KEY `event_code` (`event_code`),
  KEY `raised_last` (`raised_last`),
  KEY `object_class` (`object_class`),
  KEY `user_id` (`user_id`),
  KEY `object_id` (`object_id`),
  KEY `raised` (`raised`),
  KEY `expires` (`expires`),
  KEY `no_repeat` (`no_repeat`),
  KEY `ticket_id` (`ticket_id`),
  KEY `level` (`level`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `cart_item`
--

DROP TABLE IF EXISTS `cart_item`;


CREATE TABLE `cart_item` (
  `CICLEUNIK` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `ITCLEUNIK` int(4) unsigned DEFAULT '0',
  `CUCLEUNIK` int(4) unsigned DEFAULT '0',
  `EGCLEUNIK` int(4) unsigned DEFAULT '0',
  `Quantity` int(2) unsigned DEFAULT '0',
  `Price` decimal(8,2) DEFAULT '0.00',
  `Confirmation` tinyint(1) unsigned DEFAULT '0',
  `Origin` tinyint(1) unsigned DEFAULT '0',
  `Selection` tinyint(1) unsigned DEFAULT '0',
  `Delivery` tinyint(1) unsigned DEFAULT '0',
  `Time_Sale` time DEFAULT '00:00:00',
  `Date_Sale` date DEFAULT '0000-00-00',
  `Time_Selection` time DEFAULT '00:00:00',
  `Date_Selection` date DEFAULT '0000-00-00',
  `Expiration_Date` date DEFAULT '0000-00-00',
  `Order_View` int(4) unsigned DEFAULT '0',
  UNIQUE KEY `CICLEUNIK` (`CICLEUNIK`),
  KEY `ITCLEUNIK` (`ITCLEUNIK`),
  KEY `CUCLEUNIK` (`CUCLEUNIK`),
  KEY `EGCLEUNIK` (`EGCLEUNIK`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Item-Cart';


--
-- Table structure for table `cc_cf`
--

DROP TABLE IF EXISTS `cc_cf`;


CREATE TABLE `cc_cf` (
  `cc_id` int(11) NOT NULL,
  `cf_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `cf_rc`
--

DROP TABLE IF EXISTS `cf_rc`;


CREATE TABLE `cf_rc` (
  `rc_id` int(11) NOT NULL,
  `cf_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `class1`
--

DROP TABLE IF EXISTS `class1`;


CREATE TABLE `class1` (
  `C1CLEUNIK` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `Class_Name` varchar(40) DEFAULT NULL,
  UNIQUE KEY `C1CLEUNIK` (`C1CLEUNIK`),
  UNIQUE KEY `Class_Name` (`Class_Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Item-Category';


--
-- Table structure for table `class2`
--

DROP TABLE IF EXISTS `class2`;


CREATE TABLE `class2` (
  `C2CLEUNIK` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `C1CLEUNIK` int(4) unsigned DEFAULT '0',
  `Class_Name` varchar(40) DEFAULT NULL,
  UNIQUE KEY `C2CLEUNIK` (`C2CLEUNIK`),
  UNIQUE KEY `Class_Name` (`Class_Name`),
  KEY `C1CLEUNIK` (`C1CLEUNIK`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Item-Sub-Category';


--
-- Table structure for table `class3`
--

DROP TABLE IF EXISTS `class3`;


CREATE TABLE `class3` (
  `C3CLEUNIK` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `C2CLEUNIK` int(4) unsigned DEFAULT '0',
  `Class_Name` varchar(40) DEFAULT NULL,
  UNIQUE KEY `C3CLEUNIK` (`C3CLEUNIK`),
  UNIQUE KEY `Class_Name` (`Class_Name`),
  KEY `C2CLEUNIK` (`C2CLEUNIK`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Item-Sub-Sub-Category';


--
-- Table structure for table `component_class`
--

DROP TABLE IF EXISTS `component_class`;


CREATE TABLE `component_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_component_class_1_idx` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `components_filter`
--

DROP TABLE IF EXISTS `components_filter`;


CREATE TABLE `components_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type_filter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_components_filter_1_idx` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computer_groups`
--

DROP TABLE IF EXISTS `computer_groups`;


CREATE TABLE `computer_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `country` int(11) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `language` char(2) NOT NULL,
  `phone1` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `yim` varchar(100) DEFAULT NULL,
  `skype_im` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `title` (`title`,`description`,`address`,`email`,`yim`,`skype_im`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='//groups of computers from a customer';


--
-- Table structure for table `computer_groups_computers`
--

DROP TABLE IF EXISTS `computer_groups_computers`;


CREATE TABLE `computer_groups_computers` (
  `group_id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`computer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computer_logmein`
--

DROP TABLE IF EXISTS `computer_logmein`;


CREATE TABLE `computer_logmein` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL,
  `logmein_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `computer_id` (`computer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computer_quick_contacts`
--

DROP TABLE IF EXISTS `computer_quick_contacts`;


CREATE TABLE `computer_quick_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_time` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(100) NOT NULL DEFAULT '',
  `computer_name` varchar(100) NOT NULL DEFAULT '',
  `computer_manufacturer` varchar(100) NOT NULL DEFAULT '',
  `computer_model` varchar(100) NOT NULL DEFAULT '',
  `computer_sn` varchar(100) NOT NULL DEFAULT '',
  `net_local_ip` varchar(100) NOT NULL DEFAULT '',
  `net_gateway_ip` varchar(100) NOT NULL DEFAULT '',
  `net_mac_address` varchar(100) NOT NULL DEFAULT '',
  `net_remote_ip` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computer_stolen`
--

DROP TABLE IF EXISTS `computer_stolen`;


CREATE TABLE `computer_stolen` (
  `computer_id` int(11) NOT NULL,
  `stolen_date` int(11) DEFAULT '0',
  `alert_raised` tinyint(4) DEFAULT '0',
  `date_alert` int(11) DEFAULT '0',
  PRIMARY KEY (`computer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers`
--

DROP TABLE IF EXISTS `computers`;


CREATE TABLE `computers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `last_contact` int(11) NOT NULL DEFAULT '0',
  `alert` int(11) NOT NULL DEFAULT '0',
  `missed_cycles` int(11) NOT NULL DEFAULT '0',
  `mac_address` varchar(50) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `remote_ip` varchar(50) NOT NULL DEFAULT '',
  `request_full_update` tinyint(4) NOT NULL DEFAULT '0',
  `comments` varchar(255) DEFAULT NULL,
  `internet_down` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `is_manual` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `netbios_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `last_contact` (`last_contact`),
  KEY `alert` (`alert`),
  KEY `mac_address` (`mac_address`),
  KEY `customer_id` (`customer_id`),
  KEY `profile_id` (`profile_id`),
  KEY `type` (`type`),
  KEY `internet_down` (`internet_down`),
  KEY `location_id` (`location_id`),
  KEY `is_manual` (`is_manual`),
  KEY `date_created` (`date_created`),
  KEY `netbios_name` (`netbios_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_agent_linux_versions`
--

DROP TABLE IF EXISTS `computers_agent_linux_versions`;


CREATE TABLE `computers_agent_linux_versions` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `version` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`computer_id`),
  KEY `version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_agent_versions`
--

DROP TABLE IF EXISTS `computers_agent_versions`;


CREATE TABLE `computers_agent_versions` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `file_id` int(11) NOT NULL DEFAULT '0',
  `version` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`computer_id`,`file_id`),
  KEY `version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_blackouts`
--

DROP TABLE IF EXISTS `computers_blackouts`;


CREATE TABLE `computers_blackouts` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `start_date` int(11) NOT NULL DEFAULT '0',
  `end_date` int(11) NOT NULL DEFAULT '0',
  `comments` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`computer_id`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_items`
--

DROP TABLE IF EXISTS `computers_items`;


CREATE TABLE `computers_items` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`computer_id`,`item_id`,`field_id`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`computer_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_items_dev`
--

DROP TABLE IF EXISTS `computers_items_dev`;


CREATE TABLE `computers_items_dev` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`computer_id`,`item_id`,`field_id`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`computer_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_items_log`
--

DROP TABLE IF EXISTS `computers_items_log`;


CREATE TABLE `computers_items_log` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`computer_id`,`item_id`,`field_id`,`reported`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`computer_id`),
  KEY `item_id` (`item_id`),
  KEY `reported` (`reported`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `computers_notes`
--

DROP TABLE IF EXISTS `computers_notes`;


CREATE TABLE `computers_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `computer_id` (`computer_id`),
  KEY `user_id` (`user_id`),
  KEY `created` (`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_passwords`
--

DROP TABLE IF EXISTS `computers_passwords`;


CREATE TABLE `computers_passwords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `date_removed` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date_removed` (`date_removed`),
  KEY `computer_id` (`computer_id`),
  KEY `login` (`login`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_plan`
--

DROP TABLE IF EXISTS `computers_plan`;


CREATE TABLE `computers_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `plan_id` int(11) NOT NULL DEFAULT '0',
  `plug_id` int(11) NOT NULL DEFAULT '0',
  `shape_id` int(11) DEFAULT NULL,
  `coord_x` int(6) NOT NULL DEFAULT '0',
  `coord_y` int(6) NOT NULL DEFAULT '0',
  `screen_color` varchar(6) NOT NULL DEFAULT '',
  `computer_color` varchar(6) NOT NULL DEFAULT '',
  `keyboard_color` varchar(6) NOT NULL DEFAULT '',
  `key_color` varchar(6) NOT NULL DEFAULT '',
  `outline_color` varchar(6) NOT NULL DEFAULT '',
  `rotation_angle` int(3) NOT NULL DEFAULT '0',
  `scale` tinyint(3) NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`),
  UNIQUE KEY `computer_id` (`computer_id`),
  UNIQUE KEY `computer_plan_id` (`computer_id`,`plan_id`),
  KEY `plan_id` (`plan_id`),
  KEY `plug_id` (`plug_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_remote_services`
--

DROP TABLE IF EXISTS `computers_remote_services`;


CREATE TABLE `computers_remote_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `port` varchar(10) NOT NULL DEFAULT '',
  `comments` text,
  `is_custom` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `is_web` tinyint(4) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  `use_https` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `port` (`port`),
  KEY `service_id` (`service_id`),
  KEY `computer_id` (`computer_id`),
  KEY `is_custom` (`is_custom`),
  KEY `name` (`name`),
  KEY `is_web` (`is_web`),
  KEY `use_https` (`use_https`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_roles`
--

DROP TABLE IF EXISTS `computers_roles`;


CREATE TABLE `computers_roles` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`computer_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `computers_updating`
--

DROP TABLE IF EXISTS `computers_updating`;


CREATE TABLE `computers_updating` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`computer_id`),
  KEY `update_time` (`update_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;


CREATE TABLE `country` (
  `COCLEUNIK` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Country_Name_FR` varchar(40) DEFAULT NULL,
  `Country_Name_NL` varchar(40) DEFAULT NULL,
  `Country_Name_UK` varchar(40) DEFAULT NULL,
  `Country_Name_DE` varchar(40) DEFAULT NULL,
  `Country_Name_ES` varchar(40) DEFAULT NULL,
  `Zone_Transport` tinyint(1) unsigned DEFAULT '0',
  UNIQUE KEY `COCLEUNIK` (`COCLEUNIK`),
  UNIQUE KEY `Country_Name_DE` (`Country_Name_DE`),
  UNIQUE KEY `Country_Name_FR` (`Country_Name_FR`),
  UNIQUE KEY `Country_Name_NL` (`Country_Name_NL`),
  UNIQUE KEY `Country_Name_UK` (`Country_Name_UK`),
  UNIQUE KEY `Country_Name_ES` (`Country_Name_ES`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='List of Countries';


--
-- Table structure for table `customer_nagvis_account`
--

DROP TABLE IF EXISTS `customer_nagvis_account`;


CREATE TABLE `customer_nagvis_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `protocol` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customer_orders`
--

DROP TABLE IF EXISTS `customer_orders`;


CREATE TABLE `customer_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `erp_id` varchar(25) DEFAULT NULL,
  `subscription_num` varchar(25) DEFAULT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(100) DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `billable` tinyint(4) NOT NULL DEFAULT '0',
  `for_subscription` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `erp_id` (`erp_id`),
  KEY `subscription_num` (`subscription_num`),
  KEY `customer_id` (`customer_id`),
  KEY `date` (`date`),
  KEY `subject` (`subject`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `billable` (`billable`),
  KEY `from_subscription` (`for_subscription`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customer_template_styles`
--

DROP TABLE IF EXISTS `customer_template_styles`;


CREATE TABLE `customer_template_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `default_font_size` int(2) NOT NULL DEFAULT '11',
  `default_bg_color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `header_text_decoration` varchar(10) NOT NULL DEFAULT 'none',
  `header_text_border_color` varchar(7) NOT NULL DEFAULT '#709D19',
  `header_text_color` varchar(7) NOT NULL DEFAULT '#709D19',
  `topheader_bg_color` varchar(7) NOT NULL DEFAULT '#A6D110',
  `topheader_menu_text_color` varchar(7) NOT NULL DEFAULT '#000000',
  `menu_text_color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `table_header_bg_color` varchar(7) NOT NULL DEFAULT '#EEEEEE',
  `table_highlight_bg_color` varchar(7) NOT NULL DEFAULT '#F7F7F7',
  `left_menu_text_color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `left_menu_bg_color` varchar(7) NOT NULL DEFAULT '#A6D110',
  `tab_header_text_color` varchar(7) NOT NULL DEFAULT '#709D19',
  PRIMARY KEY (`id`),
  KEY `fk_cust` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;


CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `ERP_Name` varchar(9) NOT NULL DEFAULT '0',
  `lname` varchar(100) DEFAULT NULL,
  `fname` varchar(100) NOT NULL DEFAULT '',
  `VAT` varchar(100) NOT NULL DEFAULT '',
  `Address_I_1` varchar(100) NOT NULL DEFAULT '',
  `Address_I_2` varchar(100) NOT NULL DEFAULT '',
  `Address_I_3` varchar(100) NOT NULL DEFAULT '',
  `ZIP_I` varchar(100) NOT NULL DEFAULT '',
  `Locality_I` varchar(100) NOT NULL DEFAULT '',
  `Country_I` int(10) unsigned NOT NULL DEFAULT '0',
  `Address_D_1` varchar(100) NOT NULL DEFAULT '',
  `Address_D_2` varchar(100) NOT NULL DEFAULT '',
  `Address_D_3` varchar(100) NOT NULL DEFAULT '',
  `ZIP_D` varchar(100) NOT NULL DEFAULT '',
  `Locality_D` varchar(100) NOT NULL DEFAULT '',
  `Country_D` int(10) unsigned NOT NULL DEFAULT '0',
  `Telephone` varchar(100) NOT NULL DEFAULT '',
  `Fax` varchar(100) NOT NULL DEFAULT '',
  `EMail` varchar(100) NOT NULL DEFAULT '',
  `Language` char(2) NOT NULL DEFAULT '',
  `Shop_Pourcentage` decimal(5,2) NOT NULL DEFAULT '12.00',
  `Mailing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `has_kawacs` tinyint(4) NOT NULL DEFAULT '0',
  `has_krifs` tinyint(4) NOT NULL DEFAULT '0',
  `sla_hours` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `onhold` tinyint(4) NOT NULL DEFAULT '0',
  `no_email_alerts` tinyint(4) NOT NULL DEFAULT '0',
  `contract_type` int(11) NOT NULL DEFAULT '0',
  `erp_id` varchar(25) NOT NULL DEFAULT '',
  `erp_subscription_no` varchar(25) DEFAULT NULL,
  `contract_sub_type` tinyint(4) NOT NULL DEFAULT '0',
  `price_type` tinyint(4) NOT NULL DEFAULT '0',
  `account_manager` int(11) NOT NULL DEFAULT '6',
  PRIMARY KEY (`id`),
  KEY `has_krifs` (`has_krifs`),
  KEY `has_kawacs` (`has_kawacs`),
  KEY `ERP_Name` (`ERP_Name`),
  KEY `sla_hours` (`sla_hours`),
  KEY `active` (`active`),
  KEY `onhold` (`onhold`),
  KEY `no_email_alerts` (`no_email_alerts`),
  KEY `erp_id` (`erp_id`),
  KEY `erp_subscription_no` (`erp_subscription_no`),
  KEY `erp_id_2` (`erp_id`),
  KEY `contract_sub_type` (`contract_sub_type`),
  KEY `contract_type` (`contract_type`),
  KEY `price_type` (`price_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_allowed_ips`
--

DROP TABLE IF EXISTS `customers_allowed_ips`;


CREATE TABLE `customers_allowed_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remote_ip` varchar(50) NOT NULL DEFAULT '',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `updated_by_id` int(11) NOT NULL DEFAULT '0',
  `updated_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `remote_ip` (`remote_ip`),
  KEY `updated_by_id` (`updated_by_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_cc_recipients`
--

DROP TABLE IF EXISTS `customers_cc_recipients`;


CREATE TABLE `customers_cc_recipients` (
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`customer_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_comments`
--

DROP TABLE IF EXISTS `customers_comments`;


CREATE TABLE `customers_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_contacts`
--

DROP TABLE IF EXISTS `customers_contacts`;


CREATE TABLE `customers_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `fname` varchar(100) NOT NULL DEFAULT '',
  `lname` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `position` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `customer_id` (`customer_id`),
  KEY `name` (`fname`,`lname`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_contacts_phones`
--

DROP TABLE IF EXISTS `customers_contacts_phones`;


CREATE TABLE `customers_contacts_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `comments` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_internet_contracts`
--

DROP TABLE IF EXISTS `customers_internet_contracts`;


CREATE TABLE `customers_internet_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `contract_id` int(11) NOT NULL DEFAULT '0',
  `client_number` varchar(100) NOT NULL DEFAULT '',
  `adsl_line_number` varchar(100) NOT NULL DEFAULT '',
  `ip_range` varchar(100) NOT NULL DEFAULT '',
  `has_router` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` int(11) NOT NULL DEFAULT '0',
  `has_smtp_feed` tinyint(1) NOT NULL DEFAULT '0',
  `contract_or_login` varchar(100) NOT NULL DEFAULT '',
  `is_keysource_managed` tinyint(1) NOT NULL DEFAULT '0',
  `end_date` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `line_type` tinyint(4) NOT NULL DEFAULT '0',
  `ip_address` varchar(100) DEFAULT NULL,
  `lan_ip` varchar(100) DEFAULT NULL,
  `netmask` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL DEFAULT '',
  `is_closed` tinyint(4) NOT NULL DEFAULT '0',
  `speed_max_down` int(11) NOT NULL DEFAULT '0',
  `speed_max_up` int(11) NOT NULL DEFAULT '0',
  `speed_guaranteed_down` int(11) NOT NULL DEFAULT '0',
  `speed_guaranteed_up` int(11) NOT NULL DEFAULT '0',
  `notice_months` int(11) NOT NULL DEFAULT '0',
  `date_notified` int(11) NOT NULL DEFAULT '0',
  `suspend_notifs` tinyint(4) NOT NULL DEFAULT '0',
  `notice_days_again` int(11) NOT NULL DEFAULT '0',
  `notice_again_sent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_number` (`client_number`),
  KEY `has_router` (`has_router`),
  KEY `is_keysource_managed` (`is_keysource_managed`),
  KEY `end_date` (`end_date`),
  KEY `start_date` (`start_date`),
  KEY `contract_id` (`contract_id`),
  KEY `customer_id` (`customer_id`),
  KEY `has_smtp_feed` (`has_smtp_feed`),
  KEY `ip_range` (`ip_range`),
  KEY `line_type` (`line_type`),
  KEY `ip_address` (`ip_address`),
  KEY `lan_ip` (`lan_ip`),
  KEY `netmask` (`netmask`),
  KEY `is_closed` (`is_closed`),
  KEY `notice_months` (`notice_months`),
  KEY `date_notified` (`date_notified`),
  KEY `suspend_notifs` (`suspend_notifs`),
  KEY `notice_days_again` (`notice_days_again`),
  KEY `notice_again_sent` (`notice_again_sent`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_internet_contracts_attachments`
--

DROP TABLE IF EXISTS `customers_internet_contracts_attachments`;


CREATE TABLE `customers_internet_contracts_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_internet_contract_id` int(11) NOT NULL DEFAULT '0',
  `uploaded` int(11) NOT NULL DEFAULT '0',
  `original_filename` varchar(255) DEFAULT NULL,
  `local_filename` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_internet_contract_id` (`customer_internet_contract_id`),
  KEY `uploaded` (`uploaded`),
  KEY `local_filename` (`local_filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_photos`
--

DROP TABLE IF EXISTS `customers_photos`;


CREATE TABLE `customers_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `object_class` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `uploaded` int(11) NOT NULL DEFAULT '0',
  `original_filename` varchar(255) DEFAULT NULL,
  `local_filename` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `ext_url` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_internet_contract_id` (`customer_id`),
  KEY `uploaded` (`uploaded`),
  KEY `local_filename` (`local_filename`),
  KEY `object_class` (`object_class`),
  KEY `object_id` (`object_id`),
  KEY `subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `customers_satisfaction`
--

DROP TABLE IF EXISTS `customers_satisfaction`;


CREATE TABLE `customers_satisfaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `overall_satisfaction` int(3) NOT NULL DEFAULT '3',
  `problem_solved` tinyint(1) NOT NULL DEFAULT '1',
  `waiting_time` int(3) NOT NULL DEFAULT '3',
  `expertize` int(3) NOT NULL DEFAULT '3',
  `urgency_consideration` int(3) NOT NULL DEFAULT '3',
  `impact_consideration` int(3) NOT NULL DEFAULT '3',
  `technician_expertize` int(3) NOT NULL DEFAULT '3',
  `technician_commitment` int(3) NOT NULL DEFAULT '3',
  `time_to_solve` int(3) NOT NULL DEFAULT '3',
  `occurence` int(3) NOT NULL DEFAULT '1',
  `suggestions` text,
  `would_recommend` tinyint(1) NOT NULL DEFAULT '1',
  `date_completed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `daily_re`
--

DROP TABLE IF EXISTS `daily_re`;


CREATE TABLE `daily_re` (
  `DRCLEUNIK` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` tinyint(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Status` char(3) DEFAULT NULL,
  `ERP_FL` char(3) DEFAULT NULL,
  `ERP_TYPE` char(3) DEFAULT NULL,
  `ERPCLEUNIK` varchar(9) DEFAULT NULL,
  `IRCLEUNIK` int(10) unsigned DEFAULT NULL,
  `Description` blob,
  `Time_in` time DEFAULT NULL,
  `Time_out` time DEFAULT NULL,
  UNIQUE KEY `DRCLEUNIK` (`DRCLEUNIK`),
  KEY `Date` (`Date`),
  KEY `user_id` (`user_id`),
  KEY `user_id_date` (`user_id`,`Date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Daily Report';


--
-- Table structure for table `disabled_plugins`
--

DROP TABLE IF EXISTS `disabled_plugins`;


CREATE TABLE `disabled_plugins` (
  `plugin_key` varchar(50) NOT NULL,
  `plugin_status` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`plugin_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `discoveries`
--

DROP TABLE IF EXISTS `discoveries`;


CREATE TABLE `discoveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) NOT NULL DEFAULT '0',
  `last_discovered` int(11) NOT NULL DEFAULT '0',
  `is_fixed_ip` tinyint(4) NOT NULL DEFAULT '0',
  `matched_auto` tinyint(4) NOT NULL DEFAULT '0',
  `matched_obj_class` int(11) NOT NULL DEFAULT '0',
  `matched_obj_id` int(11) NOT NULL DEFAULT '0',
  `matched_obj_name` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `finished_ok` tinyint(4) NOT NULL DEFAULT '0',
  `duration` float NOT NULL DEFAULT '0',
  `steps` text NOT NULL,
  `mac` varchar(255) NOT NULL DEFAULT '',
  `host_name` varchar(100) NOT NULL DEFAULT '',
  `nb_name` varchar(100) NOT NULL DEFAULT '',
  `nb_workgroup` varchar(100) NOT NULL DEFAULT '',
  `nb_mac` varchar(100) NOT NULL DEFAULT '',
  `snmp_resp` tinyint(4) NOT NULL DEFAULT '0',
  `snmp_sys_object_id` varchar(100) NOT NULL DEFAULT '',
  `snmp_sys_name` varchar(255) NOT NULL DEFAULT '',
  `snmp_sys_desc` varchar(255) NOT NULL DEFAULT '',
  `snmp_sys_contact` varchar(255) NOT NULL DEFAULT '',
  `wmi_resp` tinyint(4) NOT NULL DEFAULT '0',
  `wmi_error` varchar(255) NOT NULL DEFAULT '',
  `wmi_system_type` varchar(100) NOT NULL DEFAULT '',
  `wmi_description` varchar(255) NOT NULL DEFAULT '',
  `wmi_domain` varchar(255) NOT NULL DEFAULT '',
  `wmi_domain_role` int(11) NOT NULL DEFAULT '0',
  `wmi_manufacturer` varchar(255) NOT NULL DEFAULT '',
  `wmi_model` varchar(255) NOT NULL DEFAULT '',
  `wmi_name` varchar(255) NOT NULL DEFAULT '',
  `wmi_total_phys_memory` int(11) NOT NULL DEFAULT '0',
  `wmi_user_name` varchar(100) NOT NULL DEFAULT '',
  `wmi_oem_string_array` varchar(255) NOT NULL DEFAULT '',
  `wmi_primary_owner_contact` varchar(255) NOT NULL DEFAULT '',
  `wmi_primary_owner_name` varchar(255) NOT NULL DEFAULT '',
  `wmi_os_caption` varchar(255) NOT NULL DEFAULT '',
  `wmi_os_organization` varchar(255) NOT NULL DEFAULT '',
  `wmi_os_serial_number` varchar(255) NOT NULL DEFAULT '',
  `wmi_os_csd_version` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `detail_id` (`detail_id`),
  KEY `ip` (`ip`),
  KEY `last_discovered` (`last_discovered`),
  KEY `is_fixed_ip` (`is_fixed_ip`),
  KEY `finished_ok` (`finished_ok`),
  KEY `matched_auto` (`matched_auto`),
  KEY `matched_obj_class` (`matched_obj_class`),
  KEY `matched_obj_id` (`matched_obj_id`),
  KEY `mac` (`mac`),
  KEY `host_name` (`host_name`),
  KEY `nb_name` (`nb_name`),
  KEY `nb_mac` (`nb_mac`),
  KEY `snmp_sys_object_id` (`snmp_sys_object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `discoveries_settings`
--

DROP TABLE IF EXISTS `discoveries_settings`;


CREATE TABLE `discoveries_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `disable_discoveries` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `last_discovery` int(11) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `disable_discoveries` (`disable_discoveries`),
  KEY `last_discovery` (`last_discovery`),
  KEY `duration` (`duration`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `discoveries_settings_details`
--

DROP TABLE IF EXISTS `discoveries_settings_details`;


CREATE TABLE `discoveries_settings_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `ip_start` varchar(20) DEFAULT NULL,
  `ip_end` varchar(20) DEFAULT NULL,
  `disable_wmi` tinyint(4) NOT NULL DEFAULT '0',
  `disable_snmp` tinyint(4) NOT NULL DEFAULT '0',
  `wmi_login_id` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `last_discovery` int(11) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL DEFAULT '0',
  `request_update` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `computer_id` (`computer_id`),
  KEY `ip_start` (`ip_start`),
  KEY `ip_end` (`ip_end`),
  KEY `last_discovery` (`last_discovery`),
  KEY `request_update` (`request_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `events_log_requested`
--

DROP TABLE IF EXISTS `events_log_requested`;


CREATE TABLE `events_log_requested` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `source_id` int(11) NOT NULL DEFAULT '0',
  `types` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `profile_id` (`profile_id`),
  KEY `computer_id` (`computer_id`),
  KEY `category_id` (`category_id`),
  KEY `source_id` (`source_id`),
  KEY `types` (`types`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `events_sources`
--

DROP TABLE IF EXISTS `events_sources`;


CREATE TABLE `events_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(60) DEFAULT NULL,
  `reported_first` int(11) NOT NULL DEFAULT '0',
  `reported_first_computer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id_2` (`category_id`,`name`),
  KEY `category_id` (`category_id`),
  KEY `name` (`name`),
  KEY `reported_first` (`reported_first`),
  FULLTEXT KEY `ft_name_idx` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `filter_type`
--

DROP TABLE IF EXISTS `filter_type`;


CREATE TABLE `filter_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `imap_settings`
--

DROP TABLE IF EXISTS `imap_settings`;


CREATE TABLE `imap_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server` varchar(150) NOT NULL,
  `port` varchar(10) NOT NULL,
  `encrypt` varchar(150) NOT NULL DEFAULT '',
  `mailbox` varchar(150) NOT NULL,
  `validate_cert` tinyint(1) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `assigned_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `int_rpt`
--

DROP TABLE IF EXISTS `int_rpt`;


CREATE TABLE `int_rpt` (
  `IRCLEUNIK` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `Customer_ID` int(11) DEFAULT NULL,
  `Customer_Name` varchar(40) DEFAULT NULL,
  `Contact` varchar(40) DEFAULT NULL,
  `Telephone` varchar(50) DEFAULT NULL,
  `Address_1` varchar(40) DEFAULT NULL,
  `Address_2` varchar(40) DEFAULT NULL,
  `Address_3` varchar(40) DEFAULT NULL,
  `Zip_Code` varchar(20) DEFAULT NULL,
  `City` varchar(40) DEFAULT NULL,
  `COCLEUNIK` int(10) unsigned DEFAULT NULL,
  `Engineer` varchar(40) DEFAULT NULL,
  `Description` blob,
  `To_Do` blob,
  `Remark` blob,
  `Type_Work` tinyint(1) unsigned DEFAULT NULL,
  `Price_Hour` decimal(10,2) DEFAULT NULL,
  `Price_Moving` decimal(10,2) DEFAULT NULL,
  `Date_Intervention` date DEFAULT NULL,
  `Time_in` time DEFAULT NULL,
  `Time_out` time DEFAULT NULL,
  `Time_pause` time DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL,
  UNIQUE KEY `IRCLEUNIK` (`IRCLEUNIK`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Customer_Name` (`Customer_Name`),
  KEY `Engineer` (`Engineer`),
  KEY `Date_Intervention` (`Date_Intervention`),
  KEY `Date_Time` (`Date_Intervention`,`Time_in`),
  KEY `user_id` (`user_id`),
  KEY `Engineer_Date` (`user_id`,`Date_Intervention`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Intervention Report';


--
-- Table structure for table `int_rpt_tmp`
--

DROP TABLE IF EXISTS `int_rpt_tmp`;


CREATE TABLE `int_rpt_tmp` (
  `IRCLEUNIK` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CUCLEUNIK` int(10) unsigned DEFAULT NULL,
  `EGCLEUNIK` int(10) unsigned DEFAULT NULL,
  `Customer_ID` varchar(20) DEFAULT NULL,
  `Customer_Name` varchar(40) DEFAULT NULL,
  `Conact` varchar(40) DEFAULT NULL,
  `Telephone` varchar(50) DEFAULT NULL,
  `Address_1` varchar(40) DEFAULT NULL,
  `Address_2` varchar(40) DEFAULT NULL,
  `Address_3` varchar(40) DEFAULT NULL,
  `Zip_Code` varchar(20) DEFAULT NULL,
  `City` varchar(40) DEFAULT NULL,
  `Country` varchar(40) DEFAULT NULL,
  `Engineer` varchar(40) DEFAULT NULL,
  `Description` blob,
  `To_Do` blob,
  `Remark` blob,
  `Type_Work` tinyint(1) unsigned DEFAULT NULL,
  `Price_Hour` decimal(10,0) DEFAULT NULL,
  `Price_Moving` decimal(10,0) DEFAULT NULL,
  `Date_Intervention` date DEFAULT NULL,
  `Time_in` time DEFAULT NULL,
  `Time_out` time DEFAULT NULL,
  `Time_pause` time DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL,
  UNIQUE KEY `IRCLEUNIK` (`IRCLEUNIK`),
  KEY `EGCLEUNIK` (`EGCLEUNIK`),
  KEY `CUCLEUNIK` (`CUCLEUNIK`),
  KEY `Customer_ID` (`Customer_ID`),
  KEY `Customer_Name` (`Customer_Name`),
  KEY `Engineer` (`Engineer`),
  KEY `Date_Intervention` (`Date_Intervention`),
  KEY `Date_Time` (`Date_Intervention`,`Time_in`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `intervention_locations`
--

DROP TABLE IF EXISTS `intervention_locations`;


CREATE TABLE `intervention_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `on_site` tinyint(4) NOT NULL DEFAULT '0',
  `helpdesk` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `on_site` (`on_site`),
  KEY `helpdesk` (`helpdesk`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `intervention_reports`
--

DROP TABLE IF EXISTS `intervention_reports`;


CREATE TABLE `intervention_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `approved_date` int(11) NOT NULL DEFAULT '0',
  `approved_by_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `created` (`created`),
  KEY `user_id` (`user_id`),
  KEY `centralized` (`status`),
  KEY `subject` (`subject`),
  KEY `approved_date` (`approved_date`),
  KEY `approved_by_id` (`approved_by_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `intervention_reports_details`
--

DROP TABLE IF EXISTS `intervention_reports_details`;


CREATE TABLE `intervention_reports_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intervention_report_id` int(11) NOT NULL DEFAULT '0',
  `action_type_id` int(11) NOT NULL DEFAULT '0',
  `work_time` int(11) NOT NULL DEFAULT '0',
  `bill_amount` int(11) DEFAULT NULL,
  `tbb_amount` int(11) DEFAULT NULL,
  `tbb_amount_hours` float NOT NULL DEFAULT '0',
  `bill_amount_hours` float NOT NULL DEFAULT '0',
  `intervention_date` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `customer_order_id` int(11) NOT NULL DEFAULT '0',
  `for_subscription` tinyint(4) NOT NULL DEFAULT '0',
  `billable` tinyint(4) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `intervention_report_id` (`intervention_report_id`),
  KEY `action_type_id` (`action_type_id`),
  KEY `intervention_date` (`intervention_date`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`customer_order_id`),
  KEY `for_subscription` (`for_subscription`),
  KEY `billable` (`billable`),
  KEY `location_id` (`location_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `intervention_reports_details_ids`
--

DROP TABLE IF EXISTS `intervention_reports_details_ids`;


CREATE TABLE `intervention_reports_details_ids` (
  `intervention_report_detail_id` int(11) NOT NULL DEFAULT '0',
  `ticket_detail_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`intervention_report_detail_id`,`ticket_detail_id`),
  KEY `ticket_detail_id` (`ticket_detail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `interventions_exports`
--

DROP TABLE IF EXISTS `interventions_exports`;


CREATE TABLE `interventions_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `cnt_interventions` int(11) DEFAULT NULL,
  `tbb_sum` float DEFAULT NULL,
  `md5_file` varchar(40) DEFAULT NULL,
  `requester_ip` varchar(255) DEFAULT NULL,
  `sum_bill_time` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `status` (`status`),
  KEY `md5_file` (`md5_file`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `interventions_exports_actions`
--

DROP TABLE IF EXISTS `interventions_exports_actions`;


CREATE TABLE `interventions_exports_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `export_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `request_url` text NOT NULL,
  `requester_ip` varchar(255) DEFAULT NULL,
  `result_ok` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `interventions_exports_ids`
--

DROP TABLE IF EXISTS `interventions_exports_ids`;


CREATE TABLE `interventions_exports_ids` (
  `export_id` int(11) NOT NULL DEFAULT '0',
  `intervention_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`export_id`,`intervention_id`),
  KEY `intervention_id` (`intervention_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;


CREATE TABLE `item` (
  `ITCLEUNIK` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `C1CLEUNIK` int(4) unsigned DEFAULT '0',
  `C2CLEUNIK` int(4) unsigned DEFAULT '0',
  `C3CLEUNIK` int(4) unsigned DEFAULT '0',
  `PUCLEUNIK` int(4) unsigned DEFAULT '0',
  `Description` blob,
  `ArtID1` varchar(20) DEFAULT NULL,
  `ArtID2` varchar(20) DEFAULT NULL,
  `ArtID3` varchar(20) DEFAULT NULL,
  `PartID` varchar(100) DEFAULT NULL,
  `Version` varchar(20) DEFAULT NULL,
  `Language` varchar(20) DEFAULT NULL,
  `Media` varchar(20) DEFAULT NULL,
  `Trend` varchar(20) DEFAULT NULL,
  `PriceGroup` varchar(20) DEFAULT NULL,
  `PriceCode` varchar(20) DEFAULT NULL,
  `Price_Eur_1` decimal(8,2) DEFAULT '0.00',
  `Price_Eur_2` decimal(8,2) DEFAULT '0.00',
  `Price_Eur_3` decimal(8,2) DEFAULT '0.00',
  `Stock_1` int(1) DEFAULT '0',
  `Stock_2` int(1) DEFAULT '0',
  `Stock_3` int(1) DEFAULT '0',
  `StockIndication` varchar(20) DEFAULT NULL,
  `ModifDate` varchar(16) DEFAULT NULL,
  `EANCode` varchar(12) DEFAULT NULL,
  `Attached_Doc` varchar(100) DEFAULT NULL,
  UNIQUE KEY `ITCLEUNIK` (`ITCLEUNIK`),
  KEY `C1CLEUNIK` (`C1CLEUNIK`),
  KEY `C2CLEUNIK` (`C2CLEUNIK`),
  KEY `C3CLEUNIK` (`C3CLEUNIK`),
  KEY `PUCLEUNIK` (`PUCLEUNIK`),
  KEY `ArtID1` (`ArtID1`),
  KEY `ArtID2` (`ArtID2`),
  KEY `ArtID3` (`ArtID3`),
  KEY `PartID` (`PartID`),
  KEY `C1CLEUNIK_C2CLEUNIK_C3CLEUNIK` (`C1CLEUNIK`,`C2CLEUNIK`,`C3CLEUNIK`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Item';


--
-- Table structure for table `kawacs_agent_linux_updates`
--

DROP TABLE IF EXISTS `kawacs_agent_linux_updates`;


CREATE TABLE `kawacs_agent_linux_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(100) NOT NULL DEFAULT '',
  `md5` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `date_published` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `version` (`version`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `kawacs_agent_updates`
--

DROP TABLE IF EXISTS `kawacs_agent_updates`;


CREATE TABLE `kawacs_agent_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gen_version` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `date_published` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gen_version` (`gen_version`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `kawacs_agent_updates_files`
--

DROP TABLE IF EXISTS `kawacs_agent_updates_files`;


CREATE TABLE `kawacs_agent_updates_files` (
  `version_id` int(11) NOT NULL DEFAULT '0',
  `file_id` int(11) NOT NULL DEFAULT '0',
  `version` varchar(100) NOT NULL DEFAULT '',
  `md5` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`version_id`,`file_id`),
  KEY `version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `kawacs_agent_updates_previews`
--

DROP TABLE IF EXISTS `kawacs_agent_updates_previews`;


CREATE TABLE `kawacs_agent_updates_previews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `update_id` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `update_id` (`update_id`),
  KEY `computer_id` (`computer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `kerm_ad_groups`
--

DROP TABLE IF EXISTS `kerm_ad_groups`;


CREATE TABLE `kerm_ad_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `distinguishedname` varchar(255) DEFAULT NULL,
  `description` text,
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `kerm_ad_reports`
--

DROP TABLE IF EXISTS `kerm_ad_reports`;


CREATE TABLE `kerm_ad_reports` (
  `customer_id` int(11) NOT NULL,
  `last_report` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `kerm_ad_users`
--

DROP TABLE IF EXISTS `kerm_ad_users`;


CREATE TABLE `kerm_ad_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `FirstName` varchar(200) NOT NULL,
  `MiddleInitials` varchar(6) NOT NULL,
  `LastName` varchar(200) NOT NULL,
  `DisplayName` varchar(200) NOT NULL,
  `UserPrincipalName` varchar(255) DEFAULT NULL,
  `PostalAddress` varchar(255) DEFAULT NULL,
  `MailingAddress` varchar(255) DEFAULT NULL,
  `ResidentialAddress` varchar(255) DEFAULT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `HomePhone` varchar(100) DEFAULT NULL,
  `OfficePhone` varchar(100) DEFAULT NULL,
  `Mobile` varchar(100) DEFAULT NULL,
  `Fax` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Url` varchar(100) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `UserName` varchar(100) NOT NULL,
  `Active` tinyint(100) NOT NULL,
  `DistinguishedName` varchar(100) DEFAULT NULL,
  `GroupName` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `kerm_customers_domains`
--

DROP TABLE IF EXISTS `kerm_customers_domains`;


CREATE TABLE `kerm_customers_domains` (
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `domain` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`customer_id`,`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `krifs_saved_searches`
--

DROP TABLE IF EXISTS `krifs_saved_searches`;


CREATE TABLE `krifs_saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `filter` text NOT NULL,
  `private` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `krifs_saved_searches_favorites`
--

DROP TABLE IF EXISTS `krifs_saved_searches_favorites`;


CREATE TABLE `krifs_saved_searches_favorites` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `search_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`search_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;


CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `town_id` int(11) NOT NULL DEFAULT '0',
  `street_address` text,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `type` (`type`),
  KEY `name` (`name`),
  KEY `parent_id` (`parent_id`),
  KEY `city_id` (`town_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `locations_comments`
--

DROP TABLE IF EXISTS `locations_comments`;


CREATE TABLE `locations_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `updated` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `user_id` (`user_id`),
  KEY `updated` (`updated`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `locations_fixed`
--

DROP TABLE IF EXISTS `locations_fixed`;


CREATE TABLE `locations_fixed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;



--
-- Table structure for table `mibs`
--

DROP TABLE IF EXISTS `mibs`;


CREATE TABLE `mibs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  `date_imported` int(11) NOT NULL DEFAULT '0',
  `orig_fname` varchar(100) DEFAULT NULL,
  `loaded_ok` tinyint(4) NOT NULL DEFAULT '0',
  `main_file_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `date_imported` (`date_imported`),
  KEY `orig_fname` (`orig_fname`),
  KEY `loaded_ok` (`loaded_ok`),
  KEY `main_file_id` (`main_file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `mibs_files`
--

DROP TABLE IF EXISTS `mibs_files`;


CREATE TABLE `mibs_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mib_id` int(11) NOT NULL DEFAULT '0',
  `fname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `mib_id` (`mib_id`),
  KEY `fname` (`fname`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `mibs_oids`
--

DROP TABLE IF EXISTS `mibs_oids`;


CREATE TABLE `mibs_oids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mib_id` int(11) NOT NULL DEFAULT '0',
  `oid` varchar(150) NOT NULL DEFAULT '',
  `name` varchar(250) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `ord` int(11) NOT NULL DEFAULT '0',
  `data_type` tinyint(4) NOT NULL DEFAULT '0',
  `node_type` tinyint(4) NOT NULL DEFAULT '0',
  `access` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `syntax` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mib_id_2` (`mib_id`,`oid`),
  KEY `mib_id` (`mib_id`),
  KEY `parent_id` (`parent_id`),
  KEY `level` (`level`),
  KEY `oid` (`oid`),
  KEY `data_type` (`data_type`),
  KEY `node_type` (`node_type`),
  KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `status` (`status`),
  KEY `ord` (`ord`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `mibs_oids_vals`
--

DROP TABLE IF EXISTS `mibs_oids_vals`;


CREATE TABLE `mibs_oids_vals` (
  `oid_id` int(11) NOT NULL DEFAULT '0',
  `val` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid_id`,`val`),
  KEY `val` (`val`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_alerts`
--

DROP TABLE IF EXISTS `monitor_alerts`;


CREATE TABLE `monitor_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT '0',
  `event_code` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `on_contact_only` tinyint(4) NOT NULL DEFAULT '0',
  `join_type` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_days` int(11) NOT NULL DEFAULT '0',
  `send_to` tinyint(4) NOT NULL DEFAULT '1',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `item_id` (`item_id`),
  KEY `name` (`name`),
  KEY `profile_id` (`profile_id`),
  KEY `on_contact_only` (`on_contact_only`),
  KEY `join_type` (`join_type`),
  KEY `ignore_days` (`ignore_days`),
  KEY `send_to` (`send_to`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_alerts_conditions`
--

DROP TABLE IF EXISTS `monitor_alerts_conditions`;


CREATE TABLE `monitor_alerts_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `criteria` int(11) NOT NULL DEFAULT '0',
  `value` tinytext NOT NULL,
  `value_type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `alert_id` (`alert_id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_items`
--

DROP TABLE IF EXISTS `monitor_items`;


CREATE TABLE `monitor_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `short_name` varchar(25) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `multi_values` tinyint(4) NOT NULL DEFAULT '0',
  `category_id` tinyint(4) NOT NULL DEFAULT '0',
  `default_log` tinyint(4) NOT NULL DEFAULT '0',
  `default_update` float NOT NULL DEFAULT '0',
  `main_field_id` int(11) NOT NULL DEFAULT '0',
  `treshold` int(11) NOT NULL DEFAULT '0',
  `treshold_type` tinyint(4) NOT NULL DEFAULT '0',
  `list_type` tinyint(4) NOT NULL DEFAULT '0',
  `date_show_hour` tinyint(4) NOT NULL DEFAULT '0',
  `date_show_second` tinyint(4) NOT NULL DEFAULT '0',
  `is_snmp` tinyint(4) NOT NULL DEFAULT '0',
  `snmp_oid` varchar(100) NOT NULL DEFAULT '',
  `snmp_oid_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `category_id` (`category_id`),
  KEY `short_name` (`short_name`),
  KEY `parent_id` (`parent_id`),
  KEY `list_type` (`list_type`),
  KEY `is_snmp` (`is_snmp`),
  KEY `snmp_oid_id` (`snmp_oid_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_profiles`
--

DROP TABLE IF EXISTS `monitor_profiles`;


CREATE TABLE `monitor_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `report_interval` float NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `alert_missed_cycles` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_profiles_items`
--

DROP TABLE IF EXISTS `monitor_profiles_items`;


CREATE TABLE `monitor_profiles_items` (
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `update_interval` int(11) NOT NULL DEFAULT '0',
  `log_type` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_profiles_items_periph`
--

DROP TABLE IF EXISTS `monitor_profiles_items_periph`;


CREATE TABLE `monitor_profiles_items_periph` (
  `profile_id` varchar(100) NOT NULL DEFAULT '',
  `item_id` varchar(100) NOT NULL DEFAULT '',
  `update_interval` int(11) NOT NULL DEFAULT '0',
  `log_type` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitor_profiles_periph`
--

DROP TABLE IF EXISTS `monitor_profiles_periph`;


CREATE TABLE `monitor_profiles_periph` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `report_interval` float NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `alert_missed_cycles` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `monitored_ips`
--

DROP TABLE IF EXISTS `monitored_ips`;


CREATE TABLE `monitored_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `internet_contract_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `remote_ip` varchar(50) NOT NULL DEFAULT '',
  `target_ip` varchar(50) NOT NULL DEFAULT '',
  `disabled` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `ping_ok` tinyint(4) NOT NULL DEFAULT '0',
  `processing` int(11) NOT NULL DEFAULT '0',
  `last_ping_test` int(11) NOT NULL DEFAULT '0',
  `last_traceroute_test` int(11) NOT NULL DEFAULT '0',
  `last_ping` text NOT NULL,
  `last_traceroute` text NOT NULL,
  `comments` text NOT NULL,
  `last_test_duration` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `internet_contract_id` (`internet_contract_id`),
  KEY `created` (`created`),
  KEY `user_id` (`user_id`),
  KEY `remote_ip` (`remote_ip`),
  KEY `target_ip` (`target_ip`),
  KEY `disabled` (`disabled`),
  KEY `status` (`status`),
  KEY `processing` (`processing`),
  KEY `last_ping_test` (`last_ping_test`),
  KEY `last_traceroute_test` (`last_traceroute_test`),
  KEY `ping_ok` (`ping_ok`),
  KEY `last_test_duration` (`last_test_duration`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `mremote_connection_info`
--

DROP TABLE IF EXISTS `mremote_connection_info`;


CREATE TABLE `mremote_connection_info` (
  `id` int(11) NOT NULL,
  `protocol` int(4) DEFAULT NULL,
  `port` int(10) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `mremote_connections`
--

DROP TABLE IF EXISTS `mremote_connections`;


CREATE TABLE `mremote_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `machine_type` int(4) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `computer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;


CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_code` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `raised` int(11) NOT NULL DEFAULT '0',
  `raised_last` int(11) NOT NULL DEFAULT '0',
  `raised_count` int(11) NOT NULL DEFAULT '0',
  `object_class` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `object_event_code` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `emailed_last` int(11) NOT NULL DEFAULT '0',
  `suspend_email` int(11) NOT NULL DEFAULT '0',
  `template` varchar(255) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT '0',
  `no_repeat` tinyint(4) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `show_in_console` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `object_event_code` (`object_event_code`),
  KEY `event_code` (`event_code`),
  KEY `raised_last` (`raised_last`),
  KEY `object_class` (`object_class`),
  KEY `user_id` (`user_id`),
  KEY `object_id` (`object_id`),
  KEY `raised` (`raised`),
  KEY `expires` (`expires`),
  KEY `no_repeat` (`no_repeat`),
  KEY `ticket_id` (`ticket_id`),
  KEY `level` (`level`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;



--
-- Table structure for table `notifications_customers_recipients`
--

DROP TABLE IF EXISTS `notifications_customers_recipients`;


CREATE TABLE `notifications_customers_recipients` (
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `notif_obj_class` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`customer_id`,`notif_obj_class`,`user_id`),
  KEY `is_default` (`is_default`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `notifications_customers_recipients_customers`
--

DROP TABLE IF EXISTS `notifications_customers_recipients_customers`;


CREATE TABLE `notifications_customers_recipients_customers` (
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`customer_id`,`user_id`),
  KEY `is_default` (`is_default`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `notifications_general_recipients`
--

DROP TABLE IF EXISTS `notifications_general_recipients`;


CREATE TABLE `notifications_general_recipients` (
  `notif_obj_class` int(11) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`notif_obj_class`,`user_id`),
  KEY `is_default` (`is_default`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `notifications_recipients`
--

DROP TABLE IF EXISTS `notifications_recipients`;


CREATE TABLE `notifications_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `emailed_last` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `no_repeat` tinyint(4) NOT NULL DEFAULT '0',
  `template` varchar(255) NOT NULL DEFAULT '',
  `date_read` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `notification_id` (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `emailed_last` (`emailed_last`),
  KEY `date_read` (`date_read`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `now_working`
--

DROP TABLE IF EXISTS `now_working`;


CREATE TABLE `now_working` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `since` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `since` (`since`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripheral_plink`
--

DROP TABLE IF EXISTS `peripheral_plink`;


CREATE TABLE `peripheral_plink` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `peripheral_id` int(11) NOT NULL DEFAULT '0',
  `public_ip` varchar(100) NOT NULL DEFAULT '',
  `pf_port` varchar(10) NOT NULL DEFAULT '',
  `pf_login` varchar(100) NOT NULL DEFAULT '',
  `pf_password` varchar(100) NOT NULL DEFAULT '',
  `command_base` varchar(255) NOT NULL DEFAULT '',
  `local_port` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`peripheral_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripheral_plink_services`
--

DROP TABLE IF EXISTS `peripheral_plink_services`;


CREATE TABLE `peripheral_plink_services` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `peripheral_id` int(11) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `peripheral_ip` varchar(100) NOT NULL DEFAULT '',
  `peripheral_port` varchar(10) NOT NULL DEFAULT '',
  `selected` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`peripheral_id`,`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals`
--

DROP TABLE IF EXISTS `peripherals`;


CREATE TABLE `peripherals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `class_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `snmp_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `snmp_computer_id` int(11) NOT NULL DEFAULT '0',
  `snmp_ip` varchar(50) NOT NULL DEFAULT '',
  `last_contact` int(11) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `customer_id` (`customer_id`),
  KEY `name` (`name`),
  KEY `location_id` (`location_id`),
  KEY `profile_id` (`profile_id`),
  KEY `snmp_enabled` (`snmp_enabled`),
  KEY `snmp_computer_id` (`snmp_computer_id`),
  KEY `snmp_ip` (`snmp_ip`),
  KEY `last_contact` (`last_contact`),
  KEY `date_created` (`date_created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_classes`
--

DROP TABLE IF EXISTS `peripherals_classes`;


CREATE TABLE `peripherals_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `warranty_start_field` int(11) NOT NULL DEFAULT '0',
  `warranty_end_field` int(11) NOT NULL DEFAULT '0',
  `use_warranty` tinyint(1) NOT NULL DEFAULT '0',
  `use_sn` tinyint(1) NOT NULL DEFAULT '0',
  `sn_field` int(11) NOT NULL DEFAULT '0',
  `use_web_access` tinyint(1) NOT NULL DEFAULT '0',
  `web_access_field` int(11) NOT NULL DEFAULT '0',
  `name_width` int(11) NOT NULL DEFAULT '1',
  `position` int(11) NOT NULL DEFAULT '0',
  `link_computers` tinyint(4) NOT NULL DEFAULT '0',
  `use_net_access` tinyint(4) NOT NULL DEFAULT '0',
  `net_access_ip_field` int(11) NOT NULL DEFAULT '0',
  `net_access_port_field` int(11) NOT NULL DEFAULT '0',
  `net_access_login_field` int(11) NOT NULL DEFAULT '0',
  `net_access_password_field` int(11) NOT NULL DEFAULT '0',
  `warranty_service_package_field` int(11) NOT NULL DEFAULT '0',
  `warranty_service_level_field` int(11) NOT NULL DEFAULT '0',
  `warranty_contract_number_field` int(11) NOT NULL DEFAULT '0',
  `warranty_hw_product_id_field` int(11) NOT NULL DEFAULT '0',
  `use_snmp` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `warranty_end_field` (`warranty_end_field`),
  KEY `use_warranty` (`use_warranty`),
  KEY `use_sn` (`use_sn`),
  KEY `link_computers` (`link_computers`),
  KEY `name_width` (`name_width`),
  KEY `web_access_field` (`web_access_field`),
  KEY `sn_field` (`sn_field`),
  KEY `position` (`position`),
  KEY `use_web_access` (`use_web_access`),
  KEY `warranty_start_field` (`warranty_start_field`),
  KEY `name` (`name`),
  KEY `use_net_access` (`use_net_access`),
  KEY `net_access_ip_field` (`net_access_ip_field`),
  KEY `net_access_port_field` (`net_access_port_field`),
  KEY `net_access_login_field` (`net_access_login_field`),
  KEY `net_access_password_field` (`net_access_password_field`),
  KEY `warranty_service_package_field` (`warranty_service_package_field`),
  KEY `warranty_service_level_field` (`warranty_service_level_field`),
  KEY `warranty_contract_number_field` (`warranty_contract_number_field`),
  KEY `warranty_hw_product_id_field` (`warranty_hw_product_id_field`),
  KEY `use_snmp` (`use_snmp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_classes_fields`
--

DROP TABLE IF EXISTS `peripherals_classes_fields`;


CREATE TABLE `peripherals_classes_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `in_listings` tinyint(1) NOT NULL DEFAULT '0',
  `in_reports` tinyint(1) NOT NULL DEFAULT '0',
  `display_width` tinyint(4) NOT NULL DEFAULT '1',
  `ord` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `class_id` (`class_id`),
  KEY `ord` (`ord`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_classes_profiles`
--

DROP TABLE IF EXISTS `peripherals_classes_profiles`;


CREATE TABLE `peripherals_classes_profiles` (
  `class_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`class_id`,`profile_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_classes_profiles_fields`
--

DROP TABLE IF EXISTS `peripherals_classes_profiles_fields`;


CREATE TABLE `peripherals_classes_profiles_fields` (
  `class_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `class_field_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `item_field_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`class_id`,`profile_id`,`class_field_id`),
  KEY `profile_id` (`profile_id`),
  KEY `class_field_id` (`class_field_id`),
  KEY `item_id` (`item_id`),
  KEY `item_field_id` (`item_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_computers`
--

DROP TABLE IF EXISTS `peripherals_computers`;


CREATE TABLE `peripherals_computers` (
  `peripheral_id` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`computer_id`,`peripheral_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_fields`
--

DROP TABLE IF EXISTS `peripherals_fields`;


CREATE TABLE `peripherals_fields` (
  `peripheral_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `nrc` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`peripheral_id`),
  KEY `nrc` (`nrc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_items`
--

DROP TABLE IF EXISTS `peripherals_items`;


CREATE TABLE `peripherals_items` (
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `obj_class` tinyint(4) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`obj_id`,`obj_class`,`item_id`,`field_id`),
  KEY `obj_class` (`obj_class`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`obj_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `peripherals_items_log`
--

DROP TABLE IF EXISTS `peripherals_items_log`;


CREATE TABLE `peripherals_items_log` (
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `obj_class` tinyint(4) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`obj_id`,`obj_class`,`item_id`,`field_id`,`reported`),
  KEY `obj_class` (`obj_class`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`obj_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



--
-- Table structure for table `plan`
--

DROP TABLE IF EXISTS `plan`;


CREATE TABLE `plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `plan_description` varchar(100) NOT NULL DEFAULT '',
  `plan_image` blob,
  `plan_path` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `plink`
--

DROP TABLE IF EXISTS `plink`;


CREATE TABLE `plink` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `public_ip` varchar(100) NOT NULL DEFAULT '',
  `pf_port` varchar(10) NOT NULL DEFAULT '',
  `pf_login` varchar(100) NOT NULL DEFAULT '',
  `pf_password` varchar(100) NOT NULL DEFAULT '',
  `command_base` varchar(255) NOT NULL DEFAULT '',
  `local_port` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`computer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `plink_services`
--

DROP TABLE IF EXISTS `plink_services`;


CREATE TABLE `plink_services` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `service_id` int(11) NOT NULL DEFAULT '0',
  `computer_ip` varchar(100) NOT NULL DEFAULT '',
  `computer_port` varchar(10) NOT NULL DEFAULT '',
  `selected` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`computer_id`,`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;


CREATE TABLE `plugins` (
  `plugin_key` varchar(50) NOT NULL,
  `plugin_name` varchar(50) NOT NULL DEFAULT '',
  `plugin_desc` text,
  `plugin_version` varchar(10) NOT NULL DEFAULT '1.0',
  `plugin_creator` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`plugin_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `plugs`
--

DROP TABLE IF EXISTS `plugs`;


CREATE TABLE `plugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) DEFAULT NULL,
  `plan_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(5) DEFAULT NULL,
  `coord_x` int(6) NOT NULL DEFAULT '0',
  `coord_y` int(6) NOT NULL DEFAULT '0',
  `plug_color` varchar(6) NOT NULL DEFAULT '',
  `outline_color` varchar(6) NOT NULL DEFAULT '',
  `rotation_angle` int(3) NOT NULL DEFAULT '0',
  `scale` tinyint(3) NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `profiles_alerts`
--

DROP TABLE IF EXISTS `profiles_alerts`;


CREATE TABLE `profiles_alerts` (
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `alert_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`,`alert_id`),
  KEY `alert_id` (`alert_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `profiles_periph_alerts`
--

DROP TABLE IF EXISTS `profiles_periph_alerts`;


CREATE TABLE `profiles_periph_alerts` (
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `alert_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`,`alert_id`),
  KEY `alert_id` (`alert_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;


CREATE TABLE `providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `website` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `website` (`website`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `providers_contacts`
--

DROP TABLE IF EXISTS `providers_contacts`;


CREATE TABLE `providers_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL DEFAULT '0',
  `fname` varchar(100) NOT NULL DEFAULT '',
  `lname` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fname` (`fname`,`lname`),
  KEY `provider_id` (`provider_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `providers_contacts_phones`
--

DROP TABLE IF EXISTS `providers_contacts_phones`;


CREATE TABLE `providers_contacts_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(100) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `providers_contracts`
--

DROP TABLE IF EXISTS `providers_contracts`;


CREATE TABLE `providers_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `pub_name`
--

DROP TABLE IF EXISTS `pub_name`;


CREATE TABLE `pub_name` (
  `PUCLEUNIK` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) DEFAULT NULL,
  `Logo` varchar(255) DEFAULT NULL,
  UNIQUE KEY `PUCLEUNIK` (`PUCLEUNIK`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Shop-Publisher Name';


--
-- Table structure for table `real_components`
--

DROP TABLE IF EXISTS `real_components`;


CREATE TABLE `real_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '	',
  `name` varchar(255) DEFAULT NULL,
  `detail` text,
  PRIMARY KEY (`id`),
  KEY `fk_real_components_1_idx` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `remote_access`
--

DROP TABLE IF EXISTS `remote_access`;


CREATE TABLE `remote_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `public_ip` varchar(50) NOT NULL DEFAULT '',
  `has_port_forwarding` tinyint(1) NOT NULL DEFAULT '0',
  `needs_private_key` tinyint(1) NOT NULL DEFAULT '0',
  `private_key_id` int(11) NOT NULL DEFAULT '0',
  `pf_port` varchar(50) NOT NULL DEFAULT '',
  `pf_login` varchar(100) NOT NULL DEFAULT '',
  `pf_password` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `public_ip` (`public_ip`),
  KEY `needs_private_key` (`needs_private_key`),
  KEY `has_port_forwarding` (`has_port_forwarding`),
  KEY `customer_id` (`customer_id`),
  KEY `private_key_id` (`private_key_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_ad_printers`
--

DROP TABLE IF EXISTS `removed_ad_printers`;


CREATE TABLE `removed_ad_printers` (
  `id` int(11) NOT NULL DEFAULT '0',
  `canonical_name` varchar(200) DEFAULT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `date_removed` int(11) NOT NULL DEFAULT '0',
  `reason_removed` text NOT NULL,
  `removed_by` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `asset_number` varchar(10) NOT NULL DEFAULT '',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `snmp_enabled` tinyint(4) DEFAULT NULL,
  `snmp_computer_id` int(11) NOT NULL DEFAULT '0',
  `snmp_ip` varchar(50) DEFAULT NULL,
  `last_contact` int(11) NOT NULL DEFAULT '0',
  `sn` varchar(50) NOT NULL DEFAULT '',
  `warranty_starts` int(11) NOT NULL DEFAULT '0',
  `warranty_ends` int(11) NOT NULL DEFAULT '0',
  `service_package_id` int(11) NOT NULL DEFAULT '0',
  `service_level_id` int(11) NOT NULL DEFAULT '0',
  `contract_number` varchar(255) NOT NULL DEFAULT '',
  `hw_product_id` varchar(255) NOT NULL DEFAULT '',
  `product_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `canonical_name` (`canonical_name`),
  KEY `customer_id` (`customer_id`),
  KEY `asset_number` (`asset_number`),
  KEY `profile_id` (`profile_id`),
  KEY `snmp_enabled` (`snmp_enabled`),
  KEY `location_id` (`location_id`),
  KEY `date_created` (`date_created`),
  KEY `date_removed` (`date_removed`),
  KEY `removed_by` (`removed_by`),
  KEY `snmp_computer_id` (`snmp_computer_id`),
  KEY `snmp_ip` (`snmp_ip`),
  KEY `sn` (`sn`),
  KEY `warranty_starts` (`warranty_starts`),
  KEY `warranty_ends` (`warranty_ends`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_computers`
--

DROP TABLE IF EXISTS `removed_computers`;


CREATE TABLE `removed_computers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `last_contact` int(11) NOT NULL DEFAULT '0',
  `mac_address` varchar(50) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `remote_ip` varchar(50) NOT NULL DEFAULT '',
  `comments` varchar(255) DEFAULT NULL,
  `location_id` int(11) NOT NULL DEFAULT '0',
  `is_manual` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `netbios_name` varchar(100) NOT NULL DEFAULT '',
  `date_removed` int(11) NOT NULL DEFAULT '0',
  `reason_removed` text,
  `removed_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `profile_id` (`profile_id`),
  KEY `mac_address` (`mac_address`),
  KEY `type` (`type`),
  KEY `remote_ip` (`remote_ip`),
  KEY `date_created` (`date_created`),
  KEY `netbios_name` (`netbios_name`),
  KEY `date_removed` (`date_removed`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_computers_items`
--

DROP TABLE IF EXISTS `removed_computers_items`;


CREATE TABLE `removed_computers_items` (
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`computer_id`,`item_id`,`field_id`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`computer_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_computers_notes`
--

DROP TABLE IF EXISTS `removed_computers_notes`;


CREATE TABLE `removed_computers_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `computer_id` (`computer_id`),
  KEY `user_id` (`user_id`),
  KEY `created` (`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_peripherals`
--

DROP TABLE IF EXISTS `removed_peripherals`;


CREATE TABLE `removed_peripherals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `class_id` int(11) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `date_removed` int(11) NOT NULL DEFAULT '0',
  `reason_removed` text,
  `removed_by` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `snmp_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `snmp_computer_id` int(11) NOT NULL DEFAULT '0',
  `snmp_ip` varchar(50) NOT NULL DEFAULT '',
  `last_contact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `customer_id` (`customer_id`),
  KEY `name` (`name`),
  KEY `location_id` (`location_id`),
  KEY `profile_id` (`profile_id`),
  KEY `snmp_enabled` (`snmp_enabled`),
  KEY `snmp_computer_id` (`snmp_computer_id`),
  KEY `snmp_ip` (`snmp_ip`),
  KEY `last_contact` (`last_contact`),
  KEY `date_created` (`date_created`),
  KEY `date_removed` (`date_removed`),
  KEY `removed_by` (`removed_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_peripherals_fields`
--

DROP TABLE IF EXISTS `removed_peripherals_fields`;


CREATE TABLE `removed_peripherals_fields` (
  `peripheral_id` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `nrc` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`peripheral_id`),
  KEY `nrc` (`nrc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_peripherals_items`
--

DROP TABLE IF EXISTS `removed_peripherals_items`;


CREATE TABLE `removed_peripherals_items` (
  `obj_id` int(11) NOT NULL DEFAULT '0',
  `obj_class` tinyint(4) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `nrc` int(11) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext,
  `reported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nrc`,`obj_id`,`obj_class`,`item_id`,`field_id`),
  KEY `obj_class` (`obj_class`),
  KEY `field_id` (`field_id`),
  KEY `computer_id` (`obj_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_users`
--

DROP TABLE IF EXISTS `removed_users`;


CREATE TABLE `removed_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `fname` varchar(100) NOT NULL DEFAULT '',
  `lname` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `administrator` tinyint(4) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `allow_private` tinyint(4) NOT NULL DEFAULT '0',
  `login_password` varchar(50) NOT NULL DEFAULT '',
  `erp_name` varchar(9) NOT NULL DEFAULT '',
  `restrict_customers` tinyint(4) NOT NULL DEFAULT '0',
  `erp_id` varchar(25) NOT NULL DEFAULT '',
  `erp_id_travel` varchar(25) NOT NULL DEFAULT '',
  `erp_id_service` varchar(25) NOT NULL DEFAULT '',
  `gender` tinyint(4) NOT NULL DEFAULT '0',
  `allow_newsletter` tinyint(4) NOT NULL DEFAULT '0',
  `away_recipient_id` int(11) NOT NULL DEFAULT '0',
  `is_manager` tinyint(4) NOT NULL DEFAULT '0',
  `language` tinyint(4) NOT NULL DEFAULT '0',
  `newsletter` tinyint(4) NOT NULL DEFAULT '0',
  `allow_dashboard` tinyint(4) NOT NULL DEFAULT '1',
  `merged_into_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `login_password` (`login`,`password`),
  UNIQUE KEY `login` (`login`),
  KEY `email` (`email`),
  KEY `type` (`type`),
  KEY `customer_id` (`customer_id`),
  KEY `administrator` (`administrator`),
  KEY `active` (`active`),
  KEY `allow_private` (`allow_private`),
  KEY `erp_name` (`erp_name`),
  KEY `restrict_customers` (`restrict_customers`),
  KEY `erp_id` (`erp_id`),
  KEY `erp_id_travel` (`erp_id_travel`),
  KEY `erp_id_service` (`erp_id_service`),
  KEY `gender` (`gender`),
  KEY `allow_newsletter` (`allow_newsletter`),
  KEY `away_recipient_id` (`away_recipient_id`),
  KEY `is_manager` (`is_manager`),
  KEY `language` (`language`),
  KEY `newsletter` (`newsletter`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `removed_users_customers`
--

DROP TABLE IF EXISTS `removed_users_customers`;


CREATE TABLE `removed_users_customers` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `report_chapters`
--

DROP TABLE IF EXISTS `report_chapters`;


CREATE TABLE `report_chapters` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `index` int(11) DEFAULT NULL,
  `chapter_name_UK` varchar(40) DEFAULT NULL,
  `chapter_name_FR` varchar(40) DEFAULT NULL,
  `sub_chapter_name_UK` varchar(40) DEFAULT NULL,
  `sub_chapter_name_FR` varchar(40) DEFAULT NULL,
  `report_name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;


CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `service_levels`
--

DROP TABLE IF EXISTS `service_levels`;


CREATE TABLE `service_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `snmp_sysobjids`
--

DROP TABLE IF EXISTS `snmp_sysobjids`;


CREATE TABLE `snmp_sysobjids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snmp_sys_object_id` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `snmp_sys_object_id` (`snmp_sys_object_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `software`
--

DROP TABLE IF EXISTS `software`;


CREATE TABLE `software` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `manufacturer` varchar(100) NOT NULL DEFAULT '',
  `license_types` int(11) NOT NULL DEFAULT '0',
  `in_reports` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `in_reports` (`in_reports`),
  KEY `name` (`name`),
  KEY `manufacturer` (`manufacturer`),
  KEY `license_types` (`license_types`),
  FULLTEXT KEY `name_2` (`name`,`manufacturer`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `software_licenses`
--

DROP TABLE IF EXISTS `software_licenses`;


CREATE TABLE `software_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `software_id` int(11) NOT NULL DEFAULT '0',
  `license_type` int(11) NOT NULL DEFAULT '0',
  `licenses` int(11) NOT NULL DEFAULT '0',
  `issue_date` int(11) NOT NULL DEFAULT '0',
  `exp_date` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `used` int(11) NOT NULL DEFAULT '0',
  `no_notifications` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `software_id` (`software_id`),
  KEY `used` (`used`),
  KEY `no_notifications` (`no_notifications`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `software_licenses_files`
--

DROP TABLE IF EXISTS `software_licenses_files`;


CREATE TABLE `software_licenses_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_id` int(11) DEFAULT NULL,
  `uploaded` int(11) NOT NULL DEFAULT '0',
  `original_filename` varchar(255) NOT NULL DEFAULT '',
  `local_filename` varchar(255) DEFAULT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `license_id` (`license_id`),
  KEY `uploaded` (`uploaded`),
  KEY `original_filename` (`original_filename`),
  KEY `local_filename` (`local_filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `software_licenses_sn`
--

DROP TABLE IF EXISTS `software_licenses_sn`;


CREATE TABLE `software_licenses_sn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_id` int(11) NOT NULL DEFAULT '0',
  `sn` varchar(255) NOT NULL DEFAULT '',
  `comments` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `license_id` (`license_id`),
  KEY `sn` (`sn`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `software_matches`
--

DROP TABLE IF EXISTS `software_matches`;


CREATE TABLE `software_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `software_id` int(11) NOT NULL DEFAULT '0',
  `match_type` int(11) NOT NULL DEFAULT '0',
  `expression` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `software_id` (`match_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `supplier_customers`
--

DROP TABLE IF EXISTS `supplier_customers`;


CREATE TABLE `supplier_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `lname` varchar(100) DEFAULT NULL,
  `fname` varchar(100) NOT NULL DEFAULT '',
  `VAT` varchar(100) NOT NULL DEFAULT '',
  `Address_I_1` varchar(100) NOT NULL DEFAULT '',
  `Address_I_2` varchar(100) NOT NULL DEFAULT '',
  `Address_I_3` varchar(100) NOT NULL DEFAULT '',
  `ZIP_I` varchar(100) NOT NULL DEFAULT '',
  `Locality_I` varchar(100) NOT NULL DEFAULT '',
  `Country_I` int(10) unsigned NOT NULL DEFAULT '0',
  `Address_D_1` varchar(100) NOT NULL DEFAULT '',
  `Address_D_2` varchar(100) NOT NULL DEFAULT '',
  `Address_D_3` varchar(100) NOT NULL DEFAULT '',
  `ZIP_D` varchar(100) NOT NULL DEFAULT '',
  `Locality_D` varchar(100) NOT NULL DEFAULT '',
  `Country_D` int(10) unsigned NOT NULL DEFAULT '0',
  `Telephone` varchar(100) NOT NULL DEFAULT '',
  `Fax` varchar(100) NOT NULL DEFAULT '',
  `EMail` varchar(100) NOT NULL DEFAULT '',
  `Language` char(2) NOT NULL DEFAULT '',
  `Shop_Pourcentage` decimal(5,2) NOT NULL DEFAULT '12.00',
  `Mailing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `has_kawacs` tinyint(4) NOT NULL DEFAULT '0',
  `has_krifs` tinyint(4) NOT NULL DEFAULT '0',
  `sla_hours` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `onhold` tinyint(4) NOT NULL DEFAULT '0',
  `no_email_alerts` tinyint(4) NOT NULL DEFAULT '0',
  `price_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `has_krifs` (`has_krifs`),
  KEY `has_kawacs` (`has_kawacs`),
  KEY `sla_hours` (`sla_hours`),
  KEY `active` (`active`),
  KEY `onhold` (`onhold`),
  KEY `no_email_alerts` (`no_email_alerts`),
  KEY `price_type` (`price_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;


CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `suppliers_c_customers`
--

DROP TABLE IF EXISTS `suppliers_c_customers`;


CREATE TABLE `suppliers_c_customers` (
  `id_supplier_customer` int(11) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `customer_contract_type` int(11) NOT NULL DEFAULT '0',
  `customer_sub_contract_type` tinyint(4) NOT NULL DEFAULT '0',
  `sla_hours` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id_supplier_customer`,`id_customer`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `suppliers_service_packages`
--

DROP TABLE IF EXISTS `suppliers_service_packages`;


CREATE TABLE `suppliers_service_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;


CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL DEFAULT '0',
  `ord` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `created_by_id` int(11) NOT NULL DEFAULT '0',
  `created_date` int(11) NOT NULL DEFAULT '0',
  `hour` varchar(10) NOT NULL DEFAULT '',
  `duration` varchar(10) NOT NULL DEFAULT '',
  `customer_location_id` int(11) NOT NULL DEFAULT '0',
  `date_start` int(11) NOT NULL DEFAULT '0',
  `date_end` int(11) NOT NULL DEFAULT '0',
  `modified_date` int(11) NOT NULL DEFAULT '0',
  `exchange_uid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `date` (`date`),
  KEY `ord` (`ord`),
  KEY `location_id` (`location_id`),
  KEY `created_by_id` (`created_by_id`),
  KEY `created_date` (`created_date`),
  KEY `customer_location_id` (`customer_location_id`),
  KEY `date_start` (`date_start`),
  KEY `date_end` (`date_end`),
  KEY `modified_date` (`modified_date`),
  KEY `exchange_uid` (`exchange_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tasks_attendees`
--

DROP TABLE IF EXISTS `tasks_attendees`;


CREATE TABLE `tasks_attendees` (
  `task_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `test_plugin`
--

DROP TABLE IF EXISTS `test_plugin`;


CREATE TABLE `test_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `value` varchar(200) NOT NULL DEFAULT '',
  `date_added` int(11) NOT NULL DEFAULT '0',
  `date_last_modification` int(11) NOT NULL DEFAULT '0',
  `fk_user` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;


CREATE TABLE `tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `assigned_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `source` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `deadline` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `last_modified` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `private` tinyint(4) NOT NULL DEFAULT '0',
  `deadline_notified` tinyint(4) NOT NULL DEFAULT '0',
  `escalated` int(11) NOT NULL DEFAULT '0',
  `billable` tinyint(4) NOT NULL DEFAULT '0',
  `customer_order_id` int(11) NOT NULL DEFAULT '0',
  `for_subscription` tinyint(4) NOT NULL DEFAULT '0',
  `seen_manager_id` tinyint(4) NOT NULL DEFAULT '0',
  `seen_manager_date` int(11) NOT NULL DEFAULT '0',
  `seen_manager_comments` text NOT NULL,
  `po` varchar(30) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `last_modified` (`last_modified`),
  KEY `customer_id` (`customer_id`),
  KEY `project_id` (`project_id`),
  KEY `created` (`created`),
  KEY `type` (`type`),
  KEY `source` (`source`),
  KEY `priority` (`priority`),
  KEY `user_id` (`user_id`),
  KEY `private` (`private`),
  KEY `deadline` (`deadline`),
  KEY `deadline_notified` (`deadline_notified`),
  KEY `escalated` (`escalated`),
  KEY `subject` (`subject`),
  KEY `owner_id` (`owner_id`),
  KEY `assigned_id` (`assigned_id`),
  KEY `billable` (`billable`),
  KEY `customer_order_id` (`customer_order_id`),
  KEY `for_subscription` (`for_subscription`),
  KEY `seen_manager_id` (`seen_manager_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_attachments`
--

DROP TABLE IF EXISTS `tickets_attachments`;


CREATE TABLE `tickets_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uploaded` int(11) NOT NULL DEFAULT '0',
  `original_filename` varchar(100) NOT NULL DEFAULT '',
  `local_filename` varchar(100) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `uploaded` (`uploaded`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_cc`
--

DROP TABLE IF EXISTS `tickets_cc`;


CREATE TABLE `tickets_cc` (
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ticket_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_details`
--

DROP TABLE IF EXISTS `tickets_details`;


CREATE TABLE `tickets_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `comments` text NOT NULL,
  `work_time` int(11) NOT NULL DEFAULT '0',
  `bill_time` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `reassigned_id` int(11) NOT NULL DEFAULT '0',
  `private` tinyint(4) NOT NULL DEFAULT '0',
  `activity_id` int(11) NOT NULL DEFAULT '0',
  `assigned_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `escalated` int(11) NOT NULL DEFAULT '0',
  `time_in` int(11) NOT NULL DEFAULT '0',
  `time_out` int(11) NOT NULL DEFAULT '0',
  `intervention_report_id` int(11) NOT NULL DEFAULT '0',
  `bill_time_set` tinyint(4) NOT NULL DEFAULT '0',
  `location_id` tinyint(4) NOT NULL DEFAULT '0',
  `billable` tinyint(4) NOT NULL DEFAULT '0',
  `is_continuation` tinyint(4) NOT NULL DEFAULT '0',
  `tbb_time` int(11) NOT NULL DEFAULT '0',
  `customer_order_id` int(11) NOT NULL DEFAULT '0',
  `for_subscription` tinyint(4) NOT NULL DEFAULT '0',
  `time_start_travel_to` int(11) NOT NULL DEFAULT '0',
  `time_end_travel_to` int(11) NOT NULL DEFAULT '0',
  `time_start_travel_from` int(11) NOT NULL DEFAULT '0',
  `time_end_travel_from` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `private` (`private`),
  KEY `created` (`created`),
  KEY `user_id` (`user_id`),
  KEY `assigned_id` (`assigned_id`),
  KEY `status` (`status`),
  KEY `escalated` (`escalated`),
  KEY `time_in` (`time_in`),
  KEY `time_out` (`time_out`),
  KEY `intervention_report_id` (`intervention_report_id`),
  KEY `bill_time_set` (`bill_time_set`),
  KEY `location_id` (`location_id`),
  KEY `billable` (`billable`),
  KEY `is_continuation` (`is_continuation`),
  KEY `customer_order_id` (`customer_order_id`),
  KEY `for_subscription` (`for_subscription`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_escalation_recipients`
--

DROP TABLE IF EXISTS `tickets_escalation_recipients`;


CREATE TABLE `tickets_escalation_recipients` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `default_recipient` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `default_recipient` (`default_recipient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_manual_cc`
--

DROP TABLE IF EXISTS `tickets_manual_cc`;


CREATE TABLE `tickets_manual_cc` (
  `ticket_id` int(11) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  PRIMARY KEY (`ticket_id`,`email_address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_objects`
--

DROP TABLE IF EXISTS `tickets_objects`;


CREATE TABLE `tickets_objects` (
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `object_class` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `object_id2` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ticket_id`,`object_class`,`object_id`,`object_id2`),
  KEY `object_class` (`object_class`),
  KEY `object_id` (`object_id`),
  KEY `object_id2` (`object_id2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_statuses`
--

DROP TABLE IF EXISTS `tickets_statuses`;


CREATE TABLE `tickets_statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `escalate_after` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `escalate_after` (`escalate_after`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `tickets_types`
--

DROP TABLE IF EXISTS `tickets_types`;


CREATE TABLE `tickets_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `is_customer_default` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_count` tinyint(4) NOT NULL DEFAULT '0',
  `is_billable` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `is_customer_default` (`is_customer_default`),
  KEY `ignore_count` (`ignore_count`),
  KEY `is_billable` (`is_billable`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `timesheets`
--

DROP TABLE IF EXISTS `timesheets`;


CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `close_time` int(11) NOT NULL DEFAULT '0',
  `closed_by_id` int(11) NOT NULL DEFAULT '0',
  `approved_date` int(11) NOT NULL DEFAULT '0',
  `approved_by_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `close_time` (`close_time`),
  KEY `closed_by_id` (`closed_by_id`),
  KEY `approved_date` (`approved_date`),
  KEY `approved_by_id` (`approved_by_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `timesheets_details`
--

DROP TABLE IF EXISTS `timesheets_details`;


CREATE TABLE `timesheets_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timesheet_id` int(11) NOT NULL DEFAULT '0',
  `ticket_detail_id` int(11) NOT NULL DEFAULT '0',
  `time_in` int(11) NOT NULL DEFAULT '0',
  `time_out` int(11) NOT NULL DEFAULT '0',
  `activity_id` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `detail_special_type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timesheet_id` (`timesheet_id`),
  KEY `time_in` (`time_in`),
  KEY `time_out` (`time_out`),
  KEY `activity_id` (`activity_id`),
  KEY `location_id` (`location_id`),
  KEY `ticket_detail_id` (`ticket_detail_id`),
  KEY `customer_id` (`customer_id`),
  KEY `detail_special_type` (`detail_special_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `timesheets_exports`
--

DROP TABLE IF EXISTS `timesheets_exports`;


CREATE TABLE `timesheets_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `cnt_timesheets` int(11) DEFAULT NULL,
  `work_time_sum` float NOT NULL DEFAULT '0',
  `md5_file` varchar(40) DEFAULT NULL,
  `requester_ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `status` (`status`),
  KEY `md5_file` (`md5_file`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `timesheets_exports_actions`
--

DROP TABLE IF EXISTS `timesheets_exports_actions`;


CREATE TABLE `timesheets_exports_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `export_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `request_url` text NOT NULL,
  `requester_ip` varchar(255) DEFAULT NULL,
  `result_ok` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `timesheets_exports_ids`
--

DROP TABLE IF EXISTS `timesheets_exports_ids`;


CREATE TABLE `timesheets_exports_ids` (
  `export_id` int(11) NOT NULL DEFAULT '0',
  `timesheet_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`export_id`,`timesheet_id`),
  KEY `intervention_id` (`timesheet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `user_action_log`
--

DROP TABLE IF EXISTS `user_action_log`;


CREATE TABLE `user_action_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(4) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `action_user` int(11) NOT NULL DEFAULT '0',
  `action_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `fname` varchar(100) NOT NULL DEFAULT '',
  `lname` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `administrator` tinyint(4) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `allow_private` tinyint(4) NOT NULL DEFAULT '0',
  `login_password` varchar(50) NOT NULL DEFAULT '',
  `erp_name` varchar(9) NOT NULL DEFAULT '',
  `restrict_customers` tinyint(4) NOT NULL DEFAULT '0',
  `erp_id` varchar(25) NOT NULL DEFAULT '',
  `erp_id_travel` varchar(25) NOT NULL DEFAULT '',
  `erp_id_service` varchar(25) NOT NULL DEFAULT '',
  `gender` tinyint(4) NOT NULL DEFAULT '0',
  `allow_newsletter` tinyint(4) NOT NULL DEFAULT '0',
  `away_recipient_id` int(11) NOT NULL DEFAULT '0',
  `is_manager` tinyint(4) NOT NULL DEFAULT '0',
  `language` tinyint(4) NOT NULL DEFAULT '0',
  `newsletter` tinyint(4) NOT NULL DEFAULT '0',
  `allow_dashboard` tinyint(4) NOT NULL DEFAULT '0',
  `has_kadeum` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_password` (`login`,`password`),
  UNIQUE KEY `login` (`login`),
  KEY `email` (`email`),
  KEY `type` (`type`),
  KEY `customer_id` (`customer_id`),
  KEY `administrator` (`administrator`),
  KEY `active` (`active`),
  KEY `allow_private` (`allow_private`),
  KEY `erp_name` (`erp_name`),
  KEY `restrict_customers` (`restrict_customers`),
  KEY `erp_id` (`erp_id`),
  KEY `erp_id_travel` (`erp_id_travel`),
  KEY `erp_id_service` (`erp_id_service`),
  KEY `gender` (`gender`),
  KEY `allow_newsletter` (`allow_newsletter`),
  KEY `away_recipient_id` (`away_recipient_id`),
  KEY `is_manager` (`is_manager`),
  KEY `language` (`language`),
  KEY `newsletter` (`newsletter`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

insert into `users` (id, login, fname, lname, password, email, administrator) values  (1, 'admin', 'admin', '', md5('admin1234'), 'admin@yourdomain.com', 1);

--
-- Table structure for table `users_customers`
--

DROP TABLE IF EXISTS `users_customers`;


CREATE TABLE `users_customers` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `users_customers_assigned`
--

DROP TABLE IF EXISTS `users_customers_assigned`;


CREATE TABLE `users_customers_assigned` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `users_customers_favorites`
--

DROP TABLE IF EXISTS `users_customers_favorites`;


CREATE TABLE `users_customers_favorites` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `users_exchange`
--

DROP TABLE IF EXISTS `users_exchange`;


CREATE TABLE `users_exchange` (
  `id` int(11) NOT NULL DEFAULT '0',
  `exch_login` varchar(100) NOT NULL DEFAULT '',
  `exch_email` varchar(100) NOT NULL DEFAULT '',
  `exch_ha1` varchar(50) NOT NULL DEFAULT '',
  `exch_basic` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `exch_login` (`exch_login`),
  KEY `exch_email` (`exch_email`),
  KEY `exch_ha1` (`exch_ha1`),
  KEY `exch_basic` (`exch_basic`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;


CREATE TABLE `users_groups` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `users_phones`
--

DROP TABLE IF EXISTS `users_phones`;


CREATE TABLE `users_phones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(100) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`user_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `valid_dup_names`
--

DROP TABLE IF EXISTS `valid_dup_names`;


CREATE TABLE `valid_dup_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `netbios_name` varchar(255) NOT NULL DEFAULT '',
  `computer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `netbios_name` (`netbios_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `web_access`
--

DROP TABLE IF EXISTS `web_access`;


CREATE TABLE `web_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `comments` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `date_added` int(11) NOT NULL,
  `date_modified` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `web_access_resources`
--

DROP TABLE IF EXISTS `web_access_resources`;


CREATE TABLE `web_access_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webaccess_id` int(11) NOT NULL,
  `username` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `notes` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `not_working` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` int(11) NOT NULL,
  `date_modified` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Table structure for table `work_markers`
--

DROP TABLE IF EXISTS `work_markers`;


CREATE TABLE `work_markers` (
  `user_id` int(11) NOT NULL,
  `ticket_detail_id` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `stop_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`ticket_detail_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;











-- Dump completed on 2014-03-06 10:38:22
