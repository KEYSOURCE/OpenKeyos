<?php

require_once (dirname(__FILE__).'/../lib/lib.php');
class_load ('Task');
class_load ('ExchangeInterface');

// Synchronize the Keyos tasks with Exchange
ExchangeInterface::synchronize_tasks_exchange();

?>