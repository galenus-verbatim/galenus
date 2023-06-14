<?php
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{Http, I18n, Route};
use GalenusVerbatim\Verbatim\{Verbatim};

function roman2int($str){
    $sum=0;
    $a=array('I'=>1,'V'=>5,'X'=>10,'L'=>50,'C'=>100,'D'=>500,'M'=>1000);
    $i=strlen($str);
    while($i--){
        if(isset($a[$str[$i]])){
            $num=$a[$str[$i]];
            $sum+=$num;
            while($i&&isset($a[$str[($i-1)]])&&$a[$str[($i-1)]]<$num){
                $sum-=$a[$str[--$i]];
            }
        }
    }
    return $sum;
}

$kuhn = trim(Http::par('kuhn'));
if (!$kuhn) return;
// XVIII A, 18 a
$kuhn = preg_replace("@^(18|XVIII|17|XVII) +([aAbB])@", "$1$2", $kuhn);

list($volume, $page, $line) = array_merge(preg_split("@[\., ]+@", $kuhn), array(null, null, null));
// volume
$volab = strtolower(substr($volume, -1));
if ($volab == 'a' || $volab == 'b') {
    $volume = substr($volume, 0, -1);
}
else {
    $volab = '';
}

if (!is_numeric($volume)) {
    $volume = roman2int($volume);    
}
if (!$volume || $volume < 1 || $volume > 20) {
    return;
} 
$volume = $volume . $volab;
// securit line
$line = intval($line);
$page = intval($page);
// just volume
if ($volume && $page) {
    $sql = "SELECT cts, page_start, line_start, page_end, line_end FROM doc WHERE editors = 'Kühn, Karl Gottlob' AND volume = ? AND page_start <= ? AND page_end >= ?  ORDER BY rowid";
    $qCts = Verbatim::$pdo->prepare($sql);
    $qCts->execute(array($volume, $page, $page));
}
else {
    $sql = "SELECT cts, page_start, line_start, page_end, line_end FROM doc WHERE editors = 'Kühn, Karl Gottlob' AND volume = ? ORDER BY rowid;";
    $qCts = Verbatim::$pdo->prepare($sql);
    $qCts->execute(array($volume));
}

$cts;
$res = $qCts->fetchAll(PDO::FETCH_ASSOC);

if (count($res) < 1) {
    // bad attemp to find a Kuhn ref
    // message localized
    echo '
    <article class="text">
    Impossible de trouver la référence Kühn suivante : “' . $kuhn . '”
    </article>
    ';
    http_response_code(404);
    return;
}
else if (count($res) == 1 || !$line || !$page) {
    $cts = $res[0]['cts'];
}
// discrim on line
else if (count($res) == 2) {

    if ($res[1]['page_start'] == $page && $line >= $res[1]['line_start']) {
        $cts = $res[1]['cts'];
    }
    else if ($res[0]['page_end'] == $page && $line <= $res[0]['line_end']) {
        $cts = $res[0]['cts'];
    }
    else { // data error
        $cts = $res[0]['cts'];
    }
}
else { // data error
    $cts = $res[0]['cts'];
}

if ($line) {
    $cts .= '?kuhn=' . $volume . '.' . $page . '.' . $line;
    $cts .= '#l' . $volume . '.' . $page . "." . $line;
}
else if ($page) {
    $cts .= '?kuhn=' . $volume . '.' . $page;
    $cts .= '#p' . $volume . '.' . $page;
}
if (Galenus::$config['win']) {
    $cts = preg_replace('@urn:@', Route::home_href() . 'urn/', $cts);
}
else {
    $cts = './' . $cts;
}
echo $cts;
header("Location: $cts");
exit();
