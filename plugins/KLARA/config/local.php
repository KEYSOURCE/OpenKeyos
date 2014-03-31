<?php

/**
* KLARA specific constants
* 
* Various constants to be used across the projects
*
* @package
* @subpackage Constants_KLARA
*/


/** Device connectect to phone line: Computer */
define ('PHONE_ACCESS_DEV_COMPUTER', 1);
/** Device connectect to phone line: DRAC */
define ('PHONE_ACCESS_DEV_DRAC', 2);
/** Device connectect to phone line: Peripheral */
define ('PHONE_ACCESS_DEV_PERIPHERAL', 3);

/** Name of the devices connected to computers */
$GLOBALS['PHONE_ACCESS_DEVICES'] = array (
	PHONE_ACCESS_DEV_COMPUTER => 'Computer',
	PHONE_ACCESS_DEV_DRAC => 'DRAC',
	PHONE_ACCESS_DEV_PERIPHERAL => 'Peripheral'
);

/** Remote service type: SSH */
define ('REMOTE_SERVICE_TYPE_SSH', 1);
/** Remote service type: Terminal service */
define ('REMOTE_SERVICE_TYPE_TERMINALSRV', 2);
/** Remote service type: VNC */
define ('REMOTE_SERVICE_TYPE_VNC', 3);
/** Remote service type: VMWare Console */
define ('REMOTE_SERVICE_TYPE_VMWARE', 4);
/** Remote service type: Telnet */
define ('REMOTE_SERVICE_TYPE_TELNET', 5);


/** Names of the remote service types */
$GLOBALS['REMOTE_SERVICE_NAMES'] = array (
	REMOTE_SERVICE_TYPE_SSH => 'SSH Server',
	REMOTE_SERVICE_TYPE_TELNET => 'Telnet',
	REMOTE_SERVICE_TYPE_VMWARE => 'VMWare Console',
	REMOTE_SERVICE_TYPE_TERMINALSRV => 'Terminal Service',
	REMOTE_SERVICE_TYPE_VNC => 'VNC'
);

/** Default port numbers for services */
$GLOBALS['REMOTE_SERVICES_PORTS'] = array (
	REMOTE_SERVICE_TYPE_SSH => 22,
	REMOTE_SERVICE_TYPE_TELNET => 23,
	REMOTE_SERVICE_TYPE_VMWARE => 902,
	REMOTE_SERVICE_TYPE_TERMINALSRV => 3389,
	REMOTE_SERVICE_TYPE_VNC => 5900
);

define ('LINE_TYPE_ISDN', 1);
define ('LINE_TYPE_PSTN', 2);
define ('LINE_TYPE_CABLE', 3);
define ('LINE_TYPE_TWIN', 4);

$GLOBALS['LINE_TYPES'] = array (
	LINE_TYPE_ISDN => 'ISDN',
	LINE_TYPE_PSTN => 'PSTN',
	LINE_TYPE_CABLE => 'Cable',
	LINE_TYPE_TWIN => 'TWIN'
);

/*

### Conflicting MACs
select count(c.id) as cnt, c.mac_address, c1.id, from_unixtime(c1.last_contact) as contact, i.value as name from computers c inner join computers c1 on c.mac_address=c1.mac_address inner join computers_items i on c1.id=i.computer_id AND i.item_id=1001   group by c.mac_address, c1.id having cnt > 1 order by c.mac_address, c1.last_contact desc;
+-----+-------------------+-----+---------------------+---------+
| cnt | mac_address       | id  | contact             | name    |
+-----+-------------------+-----+---------------------+---------+
|   3 | 54:55:43:44:52:00 | 198 | 2005-04-16 12:16:29 | X300-PU |
|   3 | 54:55:43:44:52:00 | 421 | 2005-04-15 17:40:09 | X300-DK |
|   3 | 54:55:43:44:52:00 | 468 | 2005-04-15 07:18:17 | X200-SK |
+-----+-------------------+-----+---------------------+---------+


### Conflicting names
select count(i.computer_id) as cnt, value, max(computer_id), min(computer_id) from computers_items i inner join computers c on i.computer_id=c.id where item_id=1001 group by i.value, c.customer_id having cnt > 1;


select c.customer_id, i.value, count(i.computer_id) as cnt, i1.computer_id from computers_items i inner join computers_items i1 on i.item_id=1001 and i.item_id=i1.item_id and i.value=i1.value inner join computers c on i1.computer_id=c.id group by i.value, i1.computer_id, c.customer_id having cnt>1 order by i.value, c.customer_id, c.id;


select c.customer_id, count(distinct c.customer_id) as custs, i.value, count(i.computer_id) as cnt, i1.computer_id, from_unixtime(c.last_contact) as contact from computers_items i inner join computers_items i1 on i.item_id=1001 and i.item_id=i1.item_id and i.value=i1.value inner join computers c on i.computer_id=c.id group by i.value,i1.computer_id having cnt>1 and cnt>custs order by i.value, c.customer_id, c.id;


### Computers without names
select c.customer_id, c.id, i.value, from_unixtime(c.last_contact) as contact from computers c left outer join computers_items i on c.id=i.computer_id and i.item_id=1001 where i.value is null or i.value="" order by c.last_contact, c.customer_id;


### Computers having more than 1 non-ended notifications
select object_id, raised, count(distinct raised) as cnt from notifications_2005_04 where ended=0 and  object_class=1  group by 1 having cnt > 1;

*/

?>