<?php declare(strict_types=1);
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{Http, I18n, Route};
use GalenusVerbatim\Verbatim\{Verbatim};

class Data {
    /** requested cts */
    public static $cts;
    /** Doc record from database */
    public static $doc;
    /** Edition record from database */
    public static $edition;
    /** init param */
    public static function init() {
        $cts = Http::par('cts');
        // hack for Apache on windows with ':'
        $cts = str_replace('urn/', 'urn:', $cts);
        self::$cts = $cts;
        $sql = "SELECT * FROM doc WHERE cts LIKE ? LIMIT 1";
        $qDoc = Verbatim::$pdo->prepare($sql);
        $qDoc->execute(array($cts . '%'));
        self::$doc = $qDoc->fetch(PDO::FETCH_ASSOC);
        // the '_' as a separator  is historic
        $cts_edition = preg_replace('@(urn:cts:[^:]+:[^:_]+).*@', '$1', $cts);
        $sql = "SELECT * FROM edition WHERE cts = ? LIMIT 1";
        $qed = Verbatim::$pdo->prepare($sql);
        $qed->execute(array($cts_edition));
        self::$edition = $qed->fetch(PDO::FETCH_ASSOC);
    }
}
Data::init();

$title = function() {
    $doc = Data::$doc;
    $edition = Data::$edition;
    if (!$doc || !$edition) return null;
    $title = '';
    $title .= $edition['authors'];
    $num = Verbatim::num($doc);
    if ($num) $title .= ', ' . $num;

    $bibl = $edition['bibl'];
    if ($bibl === null) $bibl = "";
    /* galeno centric, extract field from zotero record */
    preg_match('@<em class="title">(.*?)</em>@', $bibl, $matches);
    if (count($matches) >= 2) {
        $title .= '. ' .$matches[1];
    } else if (isset($edition['title']) && $edition['title']) {
        $title .= '. ' .$edition['title'];
    }
    /* galeno centric, extract field from zotero record */
    preg_match('@<span class="editors">(.*?)</span>@', $bibl, $matches);
    if (count($matches) >= 2) {
        $title .= $matches[1];
    } else if (isset($edition['editors']) && $edition['editors']) {
        $title .= ', ed. ' . $edition['editors'];
    }
    $title .= Verbatim::scope($doc);
    $title .= '. ' . $doc['cts'];
    $title .= ' — Galenus verbatim';
    $title = strip_tags($title);
    return $title;
};

/**
 * Build a prev/next link for a document
 */
function prevnext($col)
{
    $q = Http::par('q');
    $qstring = '';
    if ($q) $qstring = '?q=' . $q;
    $ic = ($col == 'prev')?'⟨':'⟩';
    if (isset(Data::$doc[$col]) && Data::$doc[$col]) {
        $class= 'prevnext ' . $col;
        $a = "";
        $a .= '<a class="' . $class . '"';
        $url = Data::$doc[$col];
        if (Galenus::$config['win']) $url = str_replace('urn:', 'urn/', $url);
        $a .= ' href="'. Route::home_href() . $url . $qstring . '">';
        $a .= '<div>' . $ic . '</div></a>';
        return $a;
    }
    // empty link, spacer needed
    else return '<a class="prevnext"><div> </div></a>';
}


/**
 * Build a bibl cartouche for each doc
 */
function bibl()
{
    $html = [];
    $html[] = '<header class="doc">';
    $html[] = prevnext('prev');

    $bibl = Data::$edition['bibl'];
    if ($bibl === null) $bibl = "";
    $bibl = preg_replace(
        array(
            '@<span class="scope">.*?</span>@',
            '@<span class="editors">@',
        ),
        array(
            Verbatim::scope(Data::$doc),
            ', '.Verbatim::num(Data::$doc).'$0',
        ), 
        $bibl
    );

    $html[] = $bibl;

    $html[] = prevnext('next');

    /*
    preg_match('@<h1[^>]*>.*?</h1>@im', $bibl, $matches);
    $h1 = "";
    if (isset($matches[0])) {
        $h1 =  $matches[0];
    }
    else if (isset($edition['title']) && $edition['title']) {
        $h1 =  "<h1>" . $edition['title'] . "</h1>";
    }
    $urn = '<div class="urn"><a class="urn" href="">urn:cts:greekLit:' . preg_replace('@_@', ':', $doc['clavis']) . "</a></div>\n";
    echo $urn;


    echo '
    </header>
    <header class="doc">';

    // la référence bibliographique
    echo 

    */
    $html[] = '</header>';
    return implode("\n", $html);
}

$main = function() {
    $cts = Data::$cts;
    $doc = Data::$doc;
    $edition = Data::$edition;
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', Data::$cts);
        return;
    }
    $q = Http::par('q');
    $cts = $doc['cts'];

    $forms = array();
    if ($q) {
        $field = Http::par('f', 'lem', '/lem|orth/');
        $forms = Verbatim::forms($q, $field);
    }
    $formids = array_keys($forms);

    echo '
<div class="reader">
<div class="toc">';
    // no nav
    if (!isset($edition['nav']) || ! $edition['nav']) {
        $html = '';
    }
    else {
        $html = $edition['nav'];
    }
    // no word searched
    if (!count($formids)) {
        // selected
        $html = preg_replace(
            '@ href="./' . $cts . '"@',
            '$1 class="selected"',
            $html
        );
        // the win hack
        if (Galenus::$config['win']) {
            $html = str_replace('./urn:', Route::home_href() . 'urn/', $html);
        }
        echo $html;
    }
    // calculate occurrences by chapter
    else {
        $in  = str_repeat('?,', count($formids) - 1) . '?';
        $sql = "SELECT COUNT(*) FROM tok, doc WHERE $field IN ($in) AND doc = doc.id AND cts = ?";
        $qTok =  Verbatim::$pdo->prepare($sql);
        $params = $formids;
        $i = count($params);
        // occurrences by chapter ?
        $html = preg_replace_callback(
            array(
                '@<a href="(\./)?([^"]+)">([^<]+)</a>@',
            ),
            function ($matches) use ($cts, $q, $qTok, $params, $i){

                $params[$i] = $matches[2];
                $qTok->execute($params);
                list($count) = $qTok->fetch();
                $ret = '';
                $ret .= '<a';
                if ($matches[2] == $cts) {
                    $ret .= ' class="selected"';
                }
                $href = Route::home_href();
                if (Galenus::$config['win']) {
                    $href .= str_replace('urn:', 'urn/', $matches[2]);
                }
                else {
                    $href .= $matches[2];
                }
                $ret .= ' href="' . $href . '?q=' . $q . '"';
                $ret .= '>';
                $ret .= $matches[3];
                if ($count) {
                    $ret .= ' <small>(' . $count . ' occ.)</small>';
                }
                $ret .= '</a>';
                return $ret;
            },
            $html
        );
        echo $html;
    }
    echo '
</div>';

echo '
<div class="doc text">
';
    echo bibl();
    echo '<div class="text">';

    $html = $doc['html'];

    // if a word to find, get lem_id or orth_id
    // A word to hilite
    if (count($forms)) {
        $in  = str_repeat('?,', count($formids) - 1) . '?';
        $sql = "SELECT * FROM tok WHERE $field IN ($in) AND doc = {$doc['id']}";
        $qTok =  Verbatim::$pdo->prepare($sql);
        $qTok->execute($formids);
        $offset = 0;
        while ($tok = $qTok->fetch(PDO::FETCH_ASSOC)) {
            $start = intval($tok['start']);
            $end = intval($tok['end']);
            echo mb_substr($html, $offset, $start - $offset);
            echo "<mark>";
            echo mb_substr($html, $start, $end - $start);
            echo "</mark>";
            $offset = $end;
        }
        $html = mb_substr($html, $offset, mb_strlen($html) - $offset);
    }
    // transform line breaks in links 
    // <span class="lb" data-page="1.1" data-line="1" id="l1.1.1">
    $html = preg_replace(
        [
            '@<span class="lb" data-page="([^"]*)" data-line="([^"]*)" id="([^"]*)">[^>]*</span>@', // rewrite line breaks
            '@(<(div|p)[^>]*>\s*)<br class="lb"/>@', // first line <br/>
        ],
        [
            '<br class="lb"/><a id="$3" href="#$3" class="lb">$2.</a> ',
            '$1'
        ],
        $html
    );

    echo $html;
    echo '
    </div>
    <footer class="doc">';
    echo prevnext('prev');
    echo prevnext('next');
    echo '
    </footer>
</div>';
    echo '
    <div id="pagimage">
        <header id="image_nav">
            <span id="image_prev" class="prev" href="#" class="entree"><div>⟨</div></span>
            <div id="image_title">Cliquer un n° de page pour en afficher l’image</div>
            <span id="image_next" class="next" href="#" class="entree"><div>⟩</div></span>
        </header>
        <div>
            <div id="viewcont">
                <img id="image"/>
            </div>
        </div>
    </div>';
    
    // add some javascript info for page resolution
    js_images($doc);
    echo "\n</div>";
};

function js_images(&$doc)
{
    $file = __DIR__ . '/vols.json';
    if (!file_exists($file)) return;
    $json = file_get_contents($file);
    $vols = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    
    $vars = array();

    // if not in kuhn, nothing to display ?
    if (!isset($vols['kuhn']) || !isset($vols['kuhn'][$doc['volume']])) {
        return;
    }

    $vars['kuhn'] = $vols['kuhn'][$doc['volume']];
    $vars['kuhn']['vol'] = $doc['volume'];
    $vars['kuhn']['abbr'] = 'K';
    $cts = $doc['cts'];
    // _ as a separator in urn is historic
    $cts_edition = preg_replace('@(urn:cts:[^:]+:[^:_]+).*@', '$1', $cts);
    if (!isset($vols[$cts_edition])) {
        echo "<h1>" . $cts_edition . "</h1>";
        print_r($vols);
        // log something somewhere ?
        return;
    }

    $info = $vols[$cts_edition];
    $chartier = null;
    $bale = null;
    if ($info) {
        if (isset($info['chartier'])) {
            $chartier = $vols['chartier'][$info['chartier']];
        }
        if (isset($info['bale'])) {
            $bale = $vols['bale'][$info['bale']];
        }
    }
    if ($chartier) {
        $chartier['abbr'] = 'Chart.';
        $chartier['vol'] = $info['chartier'];
        $vars['chartier'] = $chartier;
    }
    if ($bale) {
        $bale['abbr'] = 'Bas.';
        $bale['vol'] = $info['bale'];
        $vars['bale'] = $bale;
    }

    echo "<script>\n";
    foreach ($vars as $name => $dat) {
        echo 'const img' . $name .'='.json_encode($dat, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE| JSON_UNESCAPED_SLASHES).";\n";
    }
    echo "</script>\n";
}

