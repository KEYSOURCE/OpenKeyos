<?php
$t1 = time ();
require_once ('../lib/lib.php');

$q = 'SELECT distinct c.remote_ip, c.customer_id, cu.name FROM '.TBL_COMPUTERS.' c INNER JOIN ';
$q.= TBL_CUSTOMERS.' cu ON c.customer_id=cu.id ORDER BY cu.name, c.remote_ip ';
$lst = DB::db_fetch_array ($q);

for ($i=0; $i<count($lst); $i++)
{
    $ip=$lst[$i]->remote_ip;
        echo '[#'.$lst[$i]->customer_id.': '.$lst[$i]->name.']  '.$ip."\n  Computers:\n";
	    $q = 'SELECT c.id, i.value FROM computers c LEFT OUTER JOIN computers_items i ON c.id=i.computer_id ';
	        $q.= 'AND i.item_id=1001 ';
		    $q.= 'WHERE c.remote_ip="'.$ip.'" ORDER BY c.id ';
		        $comps = DB::db_fetch_list ($q);
			
			    foreach ($comps as $id=>$name)
			        {
				        echo "    #$id: ".($name?$name:'<unknown>')."\n";
					    }
					    
					        echo "\n";
						}
						
						die;
						


$q = 'SELECT distinct c.remote_ip, c.customer_id, cu.name, count(c.id) as cnt ';
$q.= 'FROM '.TBL_COMPUTERS.' c INNER JOIN ';
$q.= TBL_CUSTOMERS.' cu ON c.customer_id=cu.id GROUP BY c.remote_ip ORDER BY cu.name, c.remote_ip ';
$lst = DB::db_fetch_array ($q);

$cnt_failed=0;
for ($i=0; $i<count($lst); $i++)
{
    echo "\n";
    $ip = $lst[$i]->remote_ip;

    echo '[#'.$lst[$i]->customer_id.': '.$lst[$i]->name.']  '.$ip.' ('.$lst[$i]->cnt.' computers): Ping test: ';
    $command = "/bin/ping -w 5 -q -c 3 -i 0.2 ".$ip;
    $res = `$command`;

    if (preg_match('/100\% packet loss/',$res))
    {
	$cnt_failed++;
        echo "FAILED\nRunning traceroute:\n";
        system("/bin/traceroute -m 20 -w3 -q1 $ip");
    }
    else
    {
        echo " OK\n";
    }

}

$t2 = time() - $t1;

echo "\n==========================\n";
echo "IPs checked: ".count($lst)."\nUnreacheable IPs: ".$cnt_failed."\nTOTAL TIME: $t2 sec\n\n";

?>
