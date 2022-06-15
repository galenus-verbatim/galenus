<?php
echo "Thanks… ";
$log_file = __DIR__ . '/github.txt';
ini_set("log_errors", 1);
ini_set("error_log", $log_file);
$fopen = fopen($log_file, 'a+');

fwrite($fopen, "\n\n================\n");
fwrite($fopen, '    ## ' . date(DATE_ATOM)."\n");


$headers = apache_request_headers();
fwrite($fopen, print_r($_SERVER, true));

$body = file_get_contents('php://input');
fwrite($fopen, $body);
fclose($fopen);

echo " …End";
