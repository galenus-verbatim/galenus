<?php



// get the sha 
$sha = $SERVER['X-Github-Sha'];
// check last sha
$sha_file = '.htsha'; // should be invisible
$sha_last = file_get_contents($sha_file);
// bug or attack ?
if ($sha == $sha_last) return;
file_put_contents($sha_file, $sha);

$log_file = __DIR__ . '/github.log';
// 2Mb log ? Something is wrong
if (filesize($log_file) > 2000000) return;

$fopen = fopen($log_file, 'a+');
fwrite($fopen, date(DATE_ATOM) . ' ' . $sha . "\n");
// git pull
$lines=array();
exec("git pull 2>&1", $lines);
fwrite($fopen, implode("\n", $lines));
fclose($fopen);
