<?php
if ( !isset( $HTTP_RAW_POST_DATA ) ) {
	$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
}

$xml = str_replace("Message","Reply" , $HTTP_RAW_POST_DATA);


print((trim($xml)));
?>
