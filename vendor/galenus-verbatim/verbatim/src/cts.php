<?php
declare(strict_types=1);

/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(__DIR__ . "/Verbatim.php");

use Oeuvres\Kit\{I18n, Http};
use GalenusVerbatim\Verbatim\{Verbatim};

$urn = preg_replace('@_@', ':', trim(Http::par('URN')));
$prefix = 'urn:cts:greekLit:';
if (strpos($urn, $prefix) !== 0) {
    echo '<article class="text">', I18n::_('cts.bad', $urn), '</article>';
    http_response_code(404);
    return;
}
$clavis = preg_replace('@:@', '_', substr($urn, strlen($prefix)));
$clavis = strtok($clavis, '@'); // no passage selection

if (preg_match('@^tlg\d+\.tlg\d+$@', $clavis)) {
    $sql = "SELECT clavis FROM opus WHERE clavis = ?;";
}
else if (strpos($clavis, '_') === false) {
    $sql = "SELECT clavis FROM editio WHERE clavis = ?;";
}
else {
    $sql = "SELECT clavis FROM doc WHERE clavis = ?;";
}


$q = Verbatim::$pdo->prepare($sql);
$q->execute(array($clavis));
$res = $q->fetchAll(PDO::FETCH_ASSOC);

if (count($res) < 1) {
    // bad attemp to find a cts URN
    echo '<article class="text">', I18n::_('cts.notfound', $urn), '</article>';
    http_response_code(404);
    return;
}


echo "urn=". $urn . " url=" . $clavis;
header("Location: $clavis");
exit();
