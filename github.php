<?php 
$post = file_get_contents('php://input');
$out = fopen(__DIR__ . '/github.log', 'a+');
fwrite($out, $post . "\n\n");
fclose($out);

