<?php
require_once(dirname(__FILE__).'/../../phplib/lib.php');
class_load('WarrantyContractModel');
$brand = "Unknown";
$ws = array();
if(isset($_REQUEST['brand'])){
   if(preg_match('/Hewlett-Packard/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/(\s)*HP/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/Compaq/', $_REQUEST['brand'])) $brand = 'Hewlett-Packard';
   if(preg_match('/Dell(\s)*/', $_REQUEST['brand'])) $brand = 'Dell';
}
if($brand == 'Hewlett-Packard'){
    $ws = WarrantyContractModel::get_hp_contracts_table($_REQUEST['sn']);
    //echo json_encode($ws);

}
if($brand == 'Dell'){
    $ws = WarrantyContractModel::get_dell_contracts_table($_REQUEST['sn']);
    //echo json_encode($ws);
}
echo json_encode($ws);


?>
