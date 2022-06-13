<?php

$file = __DIR__ . '/github.log';
// avoid log attack
if (filesize($file) < 2000000) {
    $out = fopen($file, 'a+');
    fwrite($out, log_event() . "\n\n");
    fclose($out);    
}
// get the sha 



function log_event() {
    $request = '';
    /*
    $request = "$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI] $_SERVER[SERVER_PROTOCOL]\n";
  

    */
    $request .= date(DATE_ATOM) . "\n";
    $ip = $_SERVER['REMOTE_ADDR'];

    $request .= "IP: $ip\n";
    foreach (getallheaders() as $name => $value) {
        $request .= "$name: $value\n";
    }
    $request .= file_get_contents('php://input');
  
    return $request;
}

