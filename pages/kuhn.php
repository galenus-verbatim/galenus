<?php
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{Http, I18n};
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

list($volumen, $pagina, $linea) = array_merge(preg_split("@[\., ]+@", $kuhn), array(null, null, null));
// volume
$volab = strtolower(substr($volumen, -1));
if ($volab == 'a' || $volab == 'b') {
    $volumen = substr($volumen, 0, -1);
}
else {
    $volab = '';
}

if (!is_numeric($volumen)) {
    $volumen = roman2int($volumen);    
}
if (!$volumen || $volumen < 1 || $volumen > 20) {
    return;
} 
$volumen = $volumen . $volab;
// securit linea
$linea = intval($linea);
$pagina = intval($pagina);
// just volume
if ($volumen && $pagina) {
    $sql = "SELECT cts, pagde, linde, pagad, linad FROM doc WHERE editor = 'Karl Gottlob Kühn' AND volumen = ? AND pagde <= ? AND pagad >= ?  ORDER BY rowid";
    $qCts = Verbatim::$pdo->prepare($sql);
    $qCts->execute(array($volumen, $pagina, $pagina));
}
else {
    $sql = "SELECT cts, pagde, linde, pagad, linad FROM doc WHERE editor = 'Karl Gottlob Kühn' AND volumen = ? ORDER BY rowid;";
    $qCts = Verbatim::$pdo->prepare($sql);
    $qCts->execute(array($volumen));
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
else if (count($res) == 1 || !$linea || !$pagina) {
    $cts = $res[0]['cts'];
}
// discrim on line
else if (count($res) == 2) {

    if ($res[1]['pagde'] == $pagina && $linea >= $res[1]['linde']) {
        $cts = $res[1]['cts'];
    }
    else if ($res[0]['pagad'] == $pagina && $linea <= $res[0]['linad']) {
        $cts = $res[0]['cts'];
    }
    else { // data error
        $cts = $res[0]['cts'];
    }
}
else { // data error
    $cts = $res[0]['cts'];
}

if ($linea) {
    $cts .= '?kuhn=' . $volumen . '.' . $pagina . '.' . $linea;
    $cts .= '#l' . $volumen . '.' . $pagina . "." . $linea;
}
else if ($pagina) {
    $cts .= '?kuhn=' . $volumen . '.' . $pagina;
    $cts .= '#p' . $volumen . '.' . $pagina;
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
