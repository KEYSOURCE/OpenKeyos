<?php
   require_once (dirname(__FILE__).'/../lib/lib.php');
   class_load ('Warranty');
   class_load ('Computer');
   
   $query = 'select * from computers_items_log where reported>=1268820156 and reported<1268827355';
   $dd = db::db_fetch_array($query);
   //debug($dd);
   $tx="";
   foreach($dd as $d)
   {
       $tx .= "(".$d->computer_id.", ".$d->item_id.", ".$d->nrc.", ".$d->field_id.", '".mysql_escape_string($d->value)."', ".$d->reported."), ";
   }
   $tx = preg_replace("/,\s$/", "", $tx);
   debug($tx);
?>
