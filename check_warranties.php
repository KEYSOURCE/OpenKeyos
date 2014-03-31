<?php
require_once(dirname(__FILE__).'/lib/lib.php');

class_load('WarrantyContract');
$brand = "Unknown";
$ws = array();
if(isset($_REQUEST['brand'])){
   if(preg_match('/Hewlett-Packard/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/(\s)*HP/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/Compaq/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/Dell(\s)*/', $_REQUEST['brand'])) $brand = 'Dell';
}
if($brand == 'Hewlett-Packard'){
    $ws = WarrantyContract::get_hp_contracts_table($_REQUEST['sn']);
    //echo json_encode($ws);

}
if($brand == 'Dell'){
    $ws = WarrantyContract::get_dell_contracts_table($_REQUEST['sn']);
    //echo json_encode($ws);
}
echo json_encode($ws);


?>
