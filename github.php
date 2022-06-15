<?php

require_once(__DIR__.'/Galenus.php');

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{LoggerFile};


$log_file = __DIR__ . '/github.log';
// 2Mb log ? Something is wrong
if (file_exists($log_file) && filesize($log_file) > 2000000) return;

ini_set("log_errors", 1);
ini_set("error_log", $log_file);
$fopen = fopen($log_file, 'a+');

$headers = apache_request_headers();
if (!isset($headers['X-Github-Sha'])) {
    // something try hack ? Log it ? 
    fwrite($fopen, print_r($_SERVER, true));
    return;
}

// get the sha
$sha = $headers['X-Github-Sha'];

fwrite($fopen, "\n\n================\n");
fwrite($fopen, '    ## ' . date(DATE_ATOM) . ' ' . $sha . "\n");


// check last sha
$sha_file = '.htsha'; // should be invisible on the internet
$sha_last='';
if (file_exists($sha_file)) $sha_last=file_get_contents($sha_file);
// bug or attack ?
if ($sha == $sha_last) return;
file_put_contents($sha_file, $sha);


// 1. git pull verbatim
$cwd = getcwd();
chdir('../verbatim');
$output = [];
exec("git fetch --force 2>&1", $output);
fwrite($fopen, implode("\n", $output). "\n\n");
chdir($cwd);

// 2. git pull galenus
$output = [];
exec("git fetch --force 2>&1", $output);
fwrite($fopen, implode("\n", $output). "\n\n");


// 3. transform odt
$logger = new LoggerFile($log_file, LogLevel::DEBUG);
Galenus::setLogger($logger);
Galenus::pages();



fclose($fopen);
