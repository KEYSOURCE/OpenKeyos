<?php

/**
* Crontab actions - check mRemote connections
*
* Run regularily once a week, and update information on contacting computers
* preparing for mremote config export
* 
* @package
* @subpackage Crontab
*
*/

require_once ('../lib/lib.php');
class_load ('mRemoteConnection');

mRemoteConnection::fetchPublicConnections();

?>