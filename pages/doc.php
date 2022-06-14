<?php
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{I18n,Web};

class Data {
    /** requested cts */
    public static $cts;
    /** Doc record from database */
    public static $doc;
    /** Editio record from database */
    public static $editio;
    /** init param */
    public static function init() {
        $cts = Web::par('cts');
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
    /* galeno centric, extract field from zotero record */
    preg_match('@<em class="title">(.*?)</em>@', $editio['bibl'], $matches);
    if (count($matches) >= 2) {
        $title .= '. ' .$matches[1];
    } else {
        $title .= '. ' .$editio['title'];
    }
    $num = Verbatim::num($doc);
    if ($num) $title .= ', ' . $num;
    /* galeno centric, extract field from zotero record */
    preg_match('@<span class="editors">(.*?)</span>@', $editio['bibl'], $matches);
    if (count($matches) >= 2) {
        $title .= $matches[1];
    } else {
        $title .= ', ed. ' . $editio['editor'];
    }
    $title .= Verbatim::scope($doc);
    $title .= '. urn:cts:greekLit:' . preg_replace('@_@', ':', $doc['clavis']);
    $title .= ' — Galenus verbatim';
    $title = strip_tags($title);
    return $title;
}

function main() {
    $cts = Data::$cts;
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', Data::$cts);
        return;
    }
    $q = Web::par('q');
    $clavis = $doc['clavis'];

    $forms = array();
    if ($q) {
        $field = Web::par('f', 'lem', '/lem|orth/');
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
    <main>
    <header class="doc">
';
    $qstring = '';
    if ($q) $qstring = '?q=' . $q;

    $key = 'ante';
    if (isset($doc[$key]) && $doc[$key]) {
        echo '
        <a class="prevnext" href="' . $doc[$key] . $qstring . '">
            <div>⟨</div>
        </a>
';
    }
    echo preg_replace(
        array(
            '@<span class="scope">.*?</span>@',
            '@<span class="editors">@',
            '@</h1>@',
        ),
        array(
            Verbatim::scope($doc),
            ', '.Verbatim::num($doc).'$0',
            '<div class="urn"><a class="urn" href="">urn:cts:greekLit:' . preg_replace('@_@', ':', $doc['clavis']) . '</a></div>' . '$0',
        ), 
        $editio['bibl']
    );

    $key = 'post';
    if (isset($doc[$key]) && $doc[$key]) {
        echo '
        <a class="prevnext" href="' . $doc[$key] . $qstring . '">
            <div>⟩</div>
        </a>';
    }

    echo '
    </header>
    <div class="text">';

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
            echo mb_substr($html, $start, $tok['charde'] - $start);
            echo "<mark>";
            echo mb_substr($html, $tok['charde'], $tok['charad'] - $tok['charde']);
            echo "</mark>";
            $start = $tok['charad'];
        }
        echo mb_substr($html, $start, mb_strlen($html) - $start);
    }
    else {
        echo $html;
    }
    echo '
    </div>
    <footer class="doc">';
    foreach (array('ante' => '⟨', 'post' => '⟩') as $key => $char) {
        if (!isset($doc[$key]) || !$doc[$key]) continue;
        echo '<a class="prevnext ' . $key .'" href="' . $doc[$key] . $qstring . '">' . $char .'</a>';
    }
    
    echo '
    </footer>
    </main>
</div>';
    echo '
    <div id="pagimage">
        <div id="viewcont">
            <img id="image"/>
        </div>
    </div>';
    
    // add some javascript info for page resolution
    $file = dirname(__DIR__) . '/theme/vols.json';
    while (file_exists($file)) {
        $json = file_get_contents($file);
        $vols = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        
        $vars = array();

        // if not in kuhn, nothing to display ?
        if (!isset($vols['kuhn']) || !isset($vols['kuhn'][$doc['volumen']])) {
            break;
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

        break;
    }
    echo "\n</div>";
}
?>

