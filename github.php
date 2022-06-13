<?php 
$out = fopen(__DIR__ . '/github.log', 'a+');
fwrite($out, request() . "\n\n");
fclose($out);
echo "Thanks";

function request() {
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

