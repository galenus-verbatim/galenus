<?php
declare(strict_types=1);

/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(__DIR__ . "/Verbatim.php");

use Oeuvres\Kit\{I18n};
use GalenusVerbatim\Verbatim\{Verbatim};

$main = function() {
    $href = 'conc?f=lem&amp;q=%s';
    echo '
<article class="text">
<h1>Index nominum</h1>
';
    $sql = "SELECT lem AS id, lem.form AS form, COUNT(lem) AS count FROM tok, lem WHERE lem.flag = 64 AND tok.lem = lem.id GROUP BY lem ORDER BY count DESC LIMIT 500";
    $qFreqs = Verbatim::$pdo->prepare($sql);
    $qFreqs->execute();
    $i = 100;
    while ($freq = $qFreqs->fetch(PDO::FETCH_ASSOC)) {
        echo  "<div><a href=\"" 
        . sprintf($href, $freq['form']) 
        . "\">" .  $freq['form'] 
        // . "   " . $forms['deorth']
        . " <small>(" . $freq['count'] . ")</small>"
        . "</a>"
        . "</div>"
        . "\n";
    }
    echo '
    </article>
';
    };
?>
