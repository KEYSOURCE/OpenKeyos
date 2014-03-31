<?php
/*
//ini_set('display_errors',1);
$targeturl="http://fortelogics.iasi.rdsnet.ro/keyos/kawacs.php?wsdl";

$ch = curl_init($targeturl);
if (isset($HTTP_RAW_POST_DATA)){
    curl_setopt($ch, CURLOPT_POST, TRUE);
} else {
    curl_setopt($ch, CURLOPT_POST, FALSE);
}
curl_setopt($ch, CURLOPT_URL, $targeturl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS    , $HTTP_RAW_POST_DATA);
curl_setopt($ch, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
$response = curl_exec($ch);

//print ('costel');
header('content-type: text/xml; charset=utf-8');
echo $response; 
die;
 *
 */
//put the HEADER data in the heade
  //print("<pre>");
  //print_r(getallheaders());
  //print("</pre>");
$load = sys_getloadavg();
echo "Server_load: ".$load[0];
  $header = array();
  foreach(getallheaders() as $key => $value) {
    if('soapserver' == strtolower($key)) { //the case of the SOAPServer header is different by browser;
      $url = $value;
    } else {
      if('host' != strtolower($key) and 'accept-encoding'!=strtolower($key))
        $header[] = $key . ':' . $value;
    }
  }
  $url="https://193.105.15.13/kawacs.php?wsdl";
  //Start the Curl session
  $session = curl_init($url);
  //print("<pre>");
  //print_r($header);
  //print("</pre>");
  // Don't return HTTP headers. Do return the contents of the call
  curl_setopt($session , CURLOPT_HEADER , false);
  curl_setopt($session , CURLOPT_HTTPHEADER , $header);
  curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($session, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);

  //Capture all data posted
  $postdata = $GLOBALS['HTTP_RAW_POST_DATA'];

  // If it's a POST, put the POST data in the body
  if($postdata)
    curl_setopt($session , CURLOPT_POSTFIELDS , $postdata);

  // The web service returns XML. Set the Content-Type appropriately
  #header('Content-Type:text/xml');

  // Make the call
  $response = curl_exec($session);
  echo $response;
  //$header_size = curl_getinfo($session , CURLINFO_HEADER_SIZE);
  //$result = substr($response , $header_size);

  //echo $result;

  curl_close($session);
?>
