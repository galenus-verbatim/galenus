<?php declare(strict_types=1);

require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{Route, I18n, Http, Xt};
use GalenusVerbatim\Verbatim\{Verbatim};


$main = function()
{
    // lines before and after occ
    $l_ante = 4;
    $l_post = 4;

    $q = Http::par('q');
    // sanitize for display
    $qprint = htmlspecialchars($q);
    if (!$q) {
        echo I18n::_('conc.noq');
        return;
    }
    $field = Http::par('f', 'lem', '/lem|orth/');
    $forms = Verbatim::forms($q, $field);
    $formids = array_keys($forms);

    if (!count($formids)) {
        echo I18n::_('conc.nowords', $qprint);
        return;
    }
    echo '<div class="conc">'."\n";

    $qDoc =  Verbatim::$pdo->prepare("SELECT * FROM doc WHERE id = ?");
    $qEditio = Verbatim::$pdo->prepare("SELECT * FROM editio WHERE id = ?");

    $in  = str_repeat('?,', count($formids) - 1) . '?';
    $sql = "SELECT COUNT(*) FROM tok WHERE $field IN ($in)";
    $qCount =  Verbatim::$pdo->prepare($sql);
    $qCount->execute($formids);
    list($count) = $qCount->fetch();
    $mess = 'conc.lem';
    if ($field == 'orth') $mess = 'conc.orth';
    if (count($forms) > 1 ) $mess .= 's';
    echo "<header>\n";
    echo '<div class="occs">' . I18n::_('conc.search', $count, "<span title=" . json_encode($q) .">$q</span>");
    $first = true;
    echo ' (';
    // unify words
    $words = array_keys(array_flip($forms));
    $words = array_combine($words, $words);
    array_walk($words, function(&$value) {
        $value = Verbatim::deform($value);
        return $value;
    });
    asort($words);

    foreach ($words as $w => $deform) {
        if ($first) $first = false;
        else echo ', ';
        echo '<span title=' . json_encode($w) . '>' . $w . '</span>';
    }
    echo ')';
    echo '</div>' . "\n";
    echo "</header>\n";

    // order by needed, natural order is by the form search
    // TODO limit to Kühn
    $sql = "SELECT * FROM tok WHERE $field IN ($in) ORDER BY id";
    $qTok =  Verbatim::$pdo->prepare($sql);
    $qTok->execute($formids);


    $lastDoc = -1;
    $html = '';
    while ($tok = $qTok->fetch(PDO::FETCH_ASSOC)) {
        $l = $tok['linea'];
        // if no line, not kühn, for now, go next
        if (intval($l) < 1) {
            continue;
        }
        if ($tok['doc'] != $lastDoc) {
            $qDoc->execute(array($tok['doc']));
            $doc = $qDoc->fetch(PDO::FETCH_ASSOC);
            $html = $doc['html'];
            // have a list of index for each line breaks
            preg_match_all('#(<div class="l">)?<span[^>]*class="lb"[^>]*>#', $html, $matches, PREG_OFFSET_CAPTURE);
            $matches = $matches[0];
            $matches_line = 0;
            $matches_count = count($matches);
            // no line found, bad ?
            if ($matches_count < 1) {
                continue;
            }
            $qEditio->execute(array($doc['editio']));
            $editio = $qEditio->fetch(PDO::FETCH_ASSOC);
            // print_r($editio);
        }
        $href = "";
        // $href = "https://galenus-verbatim.huma-num.fr/";
        $href .= $doc['clavis'] 
            . '?f=' . $field .'&amp;q=' . $qprint
            . '#l' . $tok['pag'].'.'.$tok['linea'] 
        ;
        echo "\n\n<h2>"
            ."<em>".$editio['titulus'] ."</em>, " . Verbatim::num($doc)
            . ' (' . '<a target="_blank" href="' . $href . '">' . $tok['pag'].'.'.$tok['linea'] . ' K' . '</a>' .')'
            ."</h2>\n\n";
        // we have the char index of occurrence, translate in byte index
        $de_char = intval($tok['charde']);
        $de_byte = strlen(mb_substr($html, 0, $de_char));
        $ad_char = intval($tok['charad']);
        $ad_byte = strlen(mb_substr($html, 0, $ad_char));
        // find line of occ in the matches of line breaks
        for (;$matches_line < $matches_count; $matches_line++) {
            $matches_byte = $matches[$matches_line][1];
            if ($matches_byte >= $ad_byte) {
                break;
            }
        }
        // the line of occ is before, even the last one
        // case of 0 shoudld not arrive, let cry to check bug
        $matches_line--; 
        // get start line
        $start_line = max($matches_line - $l_ante, 0);
        $start_byte = $matches[$start_line][1];
        $ante = substr($html, $start_byte, $de_byte - $start_byte);
        echo strip($ante);
        echo '<b class="mark">' . mb_substr($html, $de_char, $ad_char - $de_char) . "</b>";
        // get end line
        $end_line = min($matches_line + $l_post + 1, $matches_count);
        // case of occ on last line
        if ($end_line == $matches_count) {
            $end_byte = strlen($html) - 1;
        }
        else {
            $end_byte = $matches[$end_line][1];
        }
        $post = substr($html, $ad_byte, $end_byte - $ad_byte);
        echo strip($post);

    }
    echo "</div>\n";

};

/**
 * Strip some tags from conc
 */
function strip(&$html) {
    return preg_replace(
        [
            '#</(p|article)>#',
            '#  +</div>#', # probably blockquote
            '#<div class="lg">#'
            // '#<span[^>]*class="lb"#',
        ], 
        [
            '',
            '',
            '',
            "<br/>\n$0",
        ], 
        $html
    );
}