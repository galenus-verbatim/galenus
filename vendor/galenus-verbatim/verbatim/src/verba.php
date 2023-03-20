<?php
declare(strict_types=1);

/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(__DIR__ . "/Verbatim.php");

use Oeuvres\Kit\{I18n, Select, Http};
use GalenusVerbatim\Verbatim\{Verbatim};

$main = function() {
    $select = new Select("percol");
    $select
        ->add("", "")
        ->add("nostop", I18n::_('nostop'))
    ;
    $percol = Http::par("percol");
    $href = 'conc?q=%s&amp;f=%s';
    echo '
<article class="table">
    <table class="table freqs">
        <caption>
            <form action="">
            ' . $select . '
            </form>
        </caption>
        <thead>
            <tr>
                <th/>
                <th>' . I18n::_('Form') . '</th>
                <th class="nb">' . I18n::_('Occs') . '</th>
                <th>' . I18n::_('Lem') . '</th>
            </tr>
        </thead>
        <tbody>';
    $where = '';
    if ($percol == 'nostop') {
        $where = ' AND orth.flag != 16 ';
    }
    $sql = "SELECT orth, COUNT(orth) AS count FROM tok GROUP BY orth ORDER BY count DESC LIMIT 1000";
    $qFreqs = Verbatim::$pdo->prepare($sql);
    $qForm = Verbatim::$pdo->prepare("
        SELECT orth.form AS orth, lem.form AS lem
            , orth.deform AS deorth, lem.deform AS delem 
        FROM orth, lem
        WHERE orth.id = ? AND orth.lem = lem.id $where
    ");

    $qFreqs->execute();
    $limit = 500;
    $n = 1;
    while ($freq = $qFreqs->fetch(PDO::FETCH_ASSOC)) {
        $orthId = $freq['orth'];
        $count = intval($freq['count']); // PDO sqlite bad typing
        $qForm->execute(array($orthId));
        $forms = $qForm->fetch(PDO::FETCH_ASSOC);
        // bug not found
        if (!isset($forms['orth'])) {
            continue;
        }
        $forms['orth'] = htmlspecialchars($forms['orth']);
        $forms['lem'] = htmlspecialchars($forms['lem']);
        echo '
    <tr>
        <td class="no">' . $n++ . '</td>
        <td class="orth"><a href="' . sprintf($href, $forms['orth'], 'orth') . '">' .  $forms['orth'] . '</a></td>
        <td class="nb">' . number_format($count, 0, ',', ' ') . '</td>
        <td class="lem"><a href="' . sprintf($href, $forms['lem'], 'lem') . '">' . $forms['lem']. '</a></td>
    </tr>';
        if ($n > $limit) break;
    }
    echo '
        </tbody>
    </table>
</article>';
};
?>
