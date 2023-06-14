<?php
declare(strict_types=1);

/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(__DIR__ . "/Verbatim.php");

use Oeuvres\Kit\{I18n,Http};
use GalenusVerbatim\Verbatim\{Verbatim};

$main = function() {
    $q = Http::par('q');
    $cts = Http::par('cts');
    $sql = "SELECT * FROM opus WHERE cts = ? LIMIT 1";
    $qDoc = Verbatim::$pdo->prepare($sql);
    $qDoc->execute(array($cts));
    $doc = $qDoc->fetch(PDO::FETCH_ASSOC);
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', $cts);
        return;
    }

    echo '
<article class="text">
';
    echo $doc['bibl'];
    echo '
</article>
';

}
?>

