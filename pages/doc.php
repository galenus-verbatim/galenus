<?php declare(strict_types=1);
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{Http, I18n};
use GalenusVerbatim\Verbatim\{Verbatim};


class Data {
    /** requested cts */
    public static $cts;
    /** Doc record from database */
    public static $doc;
    /** Editio record from database */
    public static $editio;
    /** init param */
    public static function init() {
        $cts = Http::par('cts');
        self::$cts = $cts;
        if (strpos($cts, '_') === false) { // cover
            $sql = "SELECT * FROM doc WHERE clavis LIKE ? LIMIT 1";
            $qDoc = Verbatim::$pdo->prepare($sql);
            $qDoc->execute(array($cts . '%'));
        }
        else { // should be a document
            $sql = "SELECT * FROM doc WHERE clavis LIKE ? LIMIT 1";
            $qDoc = Verbatim::$pdo->prepare($sql);
            $qDoc->execute(array($cts. '%'));
        }
        self::$doc = $qDoc->fetch(PDO::FETCH_ASSOC);
        
        $edclavis = strtok($cts, '_');
        $sql = "SELECT * FROM editio WHERE clavis = ? LIMIT 1";
        $qed = Verbatim::$pdo->prepare($sql);
        $qed->execute(array($edclavis));
        self::$editio = $qed->fetch(PDO::FETCH_ASSOC);
    }
}
Data::init();

function title() {
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc || !$editio) return null;
    $title = '';
    $title .= $editio['auctor'];
    $num = Verbatim::num($doc);
    if ($num) $title .= ', ' . $num;

    $bibl = $editio['bibl'];
    if ($bibl === null) $bibl = "";
    /* galeno centric, extract field from zotero record */
    preg_match('@<em class="title">(.*?)</em>@', $bibl, $matches);
    if (count($matches) >= 2) {
        $title .= '. ' .$matches[1];
    } else if (isset($editio['title']) && $editio['title']) {
        $title .= '. ' .$editio['title'];
    }
    /* galeno centric, extract field from zotero record */
    preg_match('@<span class="editors">(.*?)</span>@', $bibl, $matches);
    if (count($matches) >= 2) {
        $title .= $matches[1];
    } else if (isset($editio['editor']) && $editio['editor']) {
        $title .= ', ed. ' . $editio['editor'];
    }
    $title .= Verbatim::scope($doc);
    $title .= '. urn:cts:greekLit:' . preg_replace('@_@', ':', $doc['clavis']);
    $title .= ' — Galenus verbatim';
    $title = strip_tags($title);
    return $title;
}
/**
 * Build a prev/next link for a document
 */
function prevnext($direction)
{
    $q = Http::par('q');
    $qstring = '';
    if ($q) $qstring = '?q=' . $q;
    $col = ($direction == 'prev')?'ante':'post';
    $ic = ($direction == 'prev')?'⟨':'⟩';
    if (isset(Data::$doc[$col]) && Data::$doc[$col]) {
        $class= 'prevnext ' . $direction . ' ' . $col;
        return '<a class="' . $class . '" href="' . Data::$doc[$col] . $qstring . '"><div>' . $ic . '</div></a>';
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

    $bibl = Data::$editio['bibl'];
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
    else if (isset($editio['title']) && $editio['title']) {
        $h1 =  "<h1>" . $editio['title'] . "</h1>";
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
    $editio = Data::$editio;
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', Data::$cts);
        return;
    }
    $q = Http::par('q');
    $clavis = $doc['clavis'];

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
    if (!isset($editio['nav']) || ! $editio['nav']) {
    }
    // no word searched
    else if (!count($formids)) {
        $html = $editio['nav'];
        $html = preg_replace(
            '@ href="' . $cts . '"@',
            '$1 class="selected"',
            $html
        );
        echo $html;
    }
    // calculate occurrences by chapter
    else {
        $in  = str_repeat('?,', count($formids) - 1) . '?';
        $sql = "SELECT COUNT(*) FROM tok, doc WHERE $field IN ($in) AND doc = doc.id AND clavis = ?";
        $qTok =  Verbatim::$pdo->prepare($sql);
        $params = $formids;
        $i = count($params);
        // occurrences by chapter ?
        $html = preg_replace_callback(
            array(
                '@<a href="([^"]+)">([^<]+)</a>@',
            ),
            function ($matches) use ($clavis, $q, $qTok, $params, $i){
                $params[$i] = $matches[1];
                $qTok->execute($params);
                list($count) = $qTok->fetch();
                $ret = '';
                $ret .= '<a';
                if ($matches[1] == $clavis) {
                    $ret .= ' class="selected"';
                }
                $ret .= ' href="' . $matches[1] . '?q=' . $q . '"';
                $ret .= '>';
                $ret .= $matches[2];
                if ($count) {
                    $ret .= ' <small>(' . $count . ' occ.)</small>';
                }
                $ret .= '</a>';
                return $ret;
            },
            $editio['nav']
        );
        echo $html;
    }
    echo '
</div>';

echo '
<div class="doc">
    <main>';
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
        $start = 0;
        while ($tok = $qTok->fetch(PDO::FETCH_ASSOC)) {
            $charde = intval($tok['charde']);
            $charad = intval($tok['charad']);
            echo mb_substr($html, $start, $charde - $start);
            echo "<mark>";
            echo mb_substr($html, $charde, $charad - $charde);
            echo "</mark>";
            $start = $charad;
        }
        echo mb_substr($html, $start, mb_strlen($html) - $start);
    }
    else {
        echo $html;
    }
    echo '
    </div>
    <footer class="doc">';
    echo prevnext('prev');
    echo prevnext('next');
    echo '
    </footer>
    </main>
</div>';
    echo '
    <div id="pagimage">
        <header id="image_header">Titre image</header>
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
    if (!isset($vols['kuhn']) || !isset($vols['kuhn'][$doc['volumen']])) {
        return;
    }

    $vars['kuhn'] = $vols['kuhn'][$doc['volumen']];
    $vars['kuhn']['vol'] = $doc['volumen'];
    $vars['kuhn']['abbr'] = 'K';

    list($book) = explode('_', $doc['clavis']);
    $info = $vols[$book];
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

