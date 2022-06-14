<?php
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Verbatim.php");

use Oeuvres\Kit\{I18n,Web};

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

$kuhn = trim(Web::par('kuhn'));
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
    $sql = "SELECT clavis, pagde, linde, pagad, linad FROM doc WHERE editor = 'Karl Gottlob Kühn' AND volumen = ? AND pagde <= ? AND pagad >= ?  ORDER BY rowid";
    $qClav = Verbatim::$pdo->prepare($sql);
    $qClav->execute(array($volumen, $pagina, $pagina));
}
else {
    $sql = "SELECT clavis, pagde, linde, pagad, linad FROM doc WHERE editor = 'Karl Gottlob Kühn' AND volumen = ? ORDER BY rowid;";
    $qClav = Verbatim::$pdo->prepare($sql);
    $qClav->execute(array($volumen));
}

$clavis;
$res = $qClav->fetchAll(PDO::FETCH_ASSOC);

if (count($res) < 1) {
    // bad attemp to find a Kuhn ref
    echo '
    <article class="text">
    Impossible de trouver la référence Kühn suivante : “' . $kuhn . '”
    </article>
    ';
    http_response_code(404);
    return;
}
else if (count($res) == 1 || !$linea || !$pagina) {
    $clavis = $res[0]['clavis'];
}
// discrim on line
else if (count($res) == 2) {

    if ($res[1]['pagde'] == $pagina && $linea >= $res[1]['linde']) {
        $clavis = $res[1]['clavis'];
    }
    else if ($res[0]['pagad'] == $pagina && $linea <= $res[0]['linad']) {
        $clavis = $res[0]['clavis'];
    }
    else { // data error
        $clavis = $res[0]['clavis'];
    }
}
else { // data error
    $clavis = $res[0]['clavis'];
}

if ($linea) {
    $clavis .= '?kuhn=' . $volumen . '.' . $pagina . '.' . $linea;
    $clavis .= '#p' . $pagina . "." . $linea;
}
else if ($pagina) {
    $clavis .= '?kuhn=' . $volumen . '.' . $pagina;
    $clavis .= '#p' . $pagina;
}
echo $clavis;
header("Location: $clavis");
exit();
