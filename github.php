<?php 
$out = fopen(__DIR__ . '/github.log', 'a+');
fwrite($out, get_raw_http_request() . "\n\n");
fclose($out);
echo "Thanks";

function get_raw_http_request() {

    $request = "$_SERVER[REQUEST_METHOD] $_SERVER[REQUEST_URI] $_SERVER[SERVER_PROTOCOL]\n";
  
    foreach (getallheaders() as $name => $value) {
      $request .= "$name: $value\n";
    }
  
    $request .= "\n" . file_get_contents('php://input');
  
    return $request;
}

