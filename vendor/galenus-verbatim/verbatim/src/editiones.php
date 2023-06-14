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
<article class="table">
<table class="table claves">
    <caption>' . I18n::_('editio.caption') . '</caption>
    <thead>
        <tr>
            <th></th>
            <th>'. I18n::_('editio.cts') .'</th>
            <th>'. I18n::_('editio.auctor') .'</th>
            <th>'. I18n::_('editio.titulus') .'</th>
            <th>'. I18n::_('editio.editor') .'</th>
            <th>'. I18n::_('editio.volumen') .'</th>
            <th>'. I18n::_('editio.annus') .'</th>
        </tr>
    <thead>
    <tbody>
';
    $sql = "SELECT cts, auctor, titulus, editor, volumen, annuspub FROM editio";
    $q = Verbatim::$pdo->prepare($sql);
    $q->execute();
    $i = 1;
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $href = './' . $row['cts'];
        if (Verbatim::win()) $href = str_replace('./urn:', './urn/', $href);
        echo  '
        <tr>
            <td class="no">' . $i++ . '</td>
            <td class="cts"><a href="' . $href . '">' . $row['cts'] . '</a></td> 
            <td>' . $row['auctor'] . '</td>
            <td>' . $row['titulus'] . '</td>
            <td>' . $row['editor'] . '</td>
            <td>' . $row['volumen'] . '</td>
            <td>' . $row['annuspub'] . '</td>
        </tr>';
    }
    echo '
    </tbody>
</table>
';
    };
?>
