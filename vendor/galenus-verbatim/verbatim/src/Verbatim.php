<?php
declare(strict_types=1);
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
namespace GalenusVerbatim\Verbatim;

use Oeuvres\Kit\{Http, I18n, Radio, Route};
use Exception, Normalizer, PDO;

Verbatim::init();
class Verbatim
{
    /** Sqlite connection */
    public static $pdo;
    /** Something nice for betacode conversion */
    public static $lat_grc;
    /** Name of the app */
    private static $name;
    /** File path of the base */
    private static $db_file;
    /** Pb with win for url  */
    private static $win;

    /**
     * Init static fields
     */
    static public function init()
    {
        self::$win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        mb_internal_encoding("UTF-8");
        self::$lat_grc = include(__DIR__ . '/lat_grc.php');
        // test needed extension
        foreach (array('intl', 'pdo_sqlite') as $ext) {
            if (!extension_loaded($ext)) {
                $mess = "<h1>Installation problem, check your php.ini, needed extension: " . $ext . "</h1>";
                throw new Exception($mess);
            }
        }
    }

    /**
     * Is win ?
     */
    static public function win()
    {
        return self::$win;
    }

    /**
     * Rewrite some links for win
     */
    static public function cts_href($cts)
    {
        $cts = ltrim($cts, ' ./');
        $href = "./" . $cts;
        if (self::$win) $href = Route::home_href() . str_replace('./urn:', './urn/', $href);
        return $href;
    }

    /**
     * return the dir of this file, ueful for page file path
     */
    static public function dir()
    {
        return __DIR__ . '/';
    }


    /**
     * Generate sitemap, requires absolute url based of resources
     */
    static public function sitemap($url_base, $sitemap_file=null)
    {
        if (! Verbatim::$pdo) {
            $mess = "<h1>Connect your verbapie database before, ex: Verbatim::connect(__DIR__ . '/mycorpus.db');</h1>";
            throw new Exception($mess);
        }
        if (!$sitemap_file) $sitemap_file = Route::home_dir() . 'sitemap.xml';
        // regenerate sitemap.xml if needed
        if (
            file_exists($sitemap_file) 
            && filemtime($sitemap_file) > self::$db_file
        ) {
            return;
        }
        $write = fopen($sitemap_file, "w");
        fwrite($write, '<?xml version="1.0" encoding="UTF-8"?>');
        fwrite($write, '
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
        $sql = "SELECT cts FROM doc ORDER BY id;";
        $q = self::$pdo->prepare($sql);
        $q->execute(array());
        while($row = $q->fetch(PDO::FETCH_ASSOC)) {
            fwrite($write, '
    <url><loc>' . $url_base . $row['cts'] . '</loc></url>');
        }
        fwrite($write, '
</urlset>
        ');
    }

    /**
     * Name the app, used in some generated messages
     */
    static public function name($name=null)
    {
        if ($name !== null) self::$name = $name;
        return self::$name;
    }

    /**
     * Database should be connected if something is desired to be displayed
     */
    static public function connect($db_file, $persistent=false)
    {
        if (!file_exists($db_file)) {
            echo "<p>Database ".$db_file." not found</p>";
            exit();
        }
        self::$db_file = realpath($db_file);
        $dsn = "sqlite:" . $db_file;
        if ($persistent) {
            self::$pdo = new PDO($dsn, null, null, array(
                PDO::ATTR_PERSISTENT => true
            ));
            self::$pdo->exec('PRAGMA mmap_size = 1073741824;');
        }
        else {
            self::$pdo = new PDO($dsn);
        }
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // shoulkd num to string fron int fields, but…
        self::$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        self::$pdo->exec("PRAGMA temp_store = 2;");
    }

    /**
     * Returns the file path of sqlite file
     */
    static public function db_file():string
    {
        return self::$db_file;
    }

    static public function prev(&$doc, &$pars=array('q'))
    {
        return self::prevnext('prev', $doc, $pars);
    }

    static public function next(&$doc, &$pars=array('q'))
    {
        return self::prevnext('next', $doc, $pars);
    }
    /**
     * a prev / next link from database 
     */
    static private function prevnext($key, &$doc, &$pars=array('q'))
    {
        if (!isset($doc[$key]) || !$doc[$key]) return; 
        $qstring = Http::qstring($pars);
        $chars = array('prev' => '⟨', 'next' => '⟩');
        echo '<a class="prevnext ' . $key .'" href="' . Verbatim::cts_href($doc[$key]) . $qstring . '">' . $chars[$key] .'</a>';
    }

    /**
     * Hilite doc
     */
    static public function hidoc(&$doc,&$formids)
    {
        $html = $doc['html'];
        if (!count($formids)) {
            return $html;
        }
        // http param a bit hard coded here
        $f = Http::par('f', 'lem', '/lem|orth/');
        $out = '';
        // Words to hilite
        $in  = str_repeat('?,', count($formids) - 1) . '?';
        $sql = "SELECT * FROM tok WHERE $f IN ($in) AND doc = {$doc['id']}";
        $qTok =  Verbatim::$pdo->prepare($sql);
        $qTok->execute($formids);
        $offset = 0;
        while ($tok = $qTok->fetch(PDO::FETCH_ASSOC)) {
            // be careful, PDO output all fields as String, fixed in php 8.1
            $start = intval($tok['start']);
            $end = intval($tok['end']);
            $out .= mb_substr($html, $offset, $start - $offset);
            $out .= "<mark>";
            $out .= mb_substr($html, $start, $end - $start);
            $out .= "</mark>";
            $offset = $end;
        }
        // $out .= mb_substr($html, $start, mb_strlen($html) - $start);
        return $out;
    }

    /**
     * Normalize a greek form to lower with no accents
     */
    static public function deform($form)
    {
        $form = Normalizer::normalize($form, Normalizer::FORM_D);
        $form = preg_replace( '@\pM@u', "", $form);
        $form = mb_strtolower($form);
        return $form;
    }

    /**
     * $q = "kai"
     */
    public static function forms(string $q, string $field)
    {
        $limit = 500; // search more than 100 words ?
        $qform = Verbatim::$pdo->prepare("SELECT id, form, cat FROM $field WHERE form LIKE ?");
        $qdeform = Verbatim::$pdo->prepare("SELECT id, form, cat FROM $field WHERE deform LIKE ?");
        $forms = array();
        $words = preg_split("@[\s,]+@", trim($q));
        for ($i = 0; $i < count($words); $i++) {
            $w = $words[$i];
            $w = Normalizer::normalize($w, Normalizer::FORM_KC);
            $w = str_replace(array('*', '?'), array('%', '_'), $w);

            /*
            if ($field == 'lem' && $w == 'NUM');
            // maybe latin letters to translitterate
            else $w = strtr($w, self::$lat_grc);
            */
            $qform->execute(array($w));
            // rowcount do not work
            $found = false;
            while ($row = $qform->fetch(PDO::FETCH_NUM)) {
                $forms[$row[0]] = $row[1]; // .' ' . I18n::_('pos.' . $row[2]) ;
                $found = true;
                if (--$limit <= 0) return $forms;
            }
            // direct forms found, go next
            if ($found) continue;

            // nothing found in form, try deform (without accents)
            if (!$row) {
                // decompose letters and accents
                $w = Normalizer::normalize($w, Normalizer::FORM_D);
                // strip non letter (accents), but keep wildcards
                $w = preg_replace("/[^\pL_%]/u", '', $w);
                // lower case folding, should regule final ς
                $w = mb_convert_case($w, MB_CASE_FOLD, "UTF-8");
                // translitterate possible beta code
                $w = strtr($w, self::$lat_grc);
                $qdeform->execute(array($w));
                while ($row = $qdeform->fetch(PDO::FETCH_NUM)) {
                    $forms[$row[0]] = $row[1]; // .' ' . I18n::_('pos.' . $row[2]) ;
                    if (--$limit <= 0) return $forms;
                }
            }
        }
        return $forms;
    }

    /**
     * Display nav with possible freqs by chapter
     */
    public static function nav(&$edition, &$doc, &$formids)
    {
        // http param a bit hard coded here
        $q = Http::par('q');
        $f = Http::par('f', 'lem', '/lem|orth/');
        $cts = $doc['cts'];
        if (!isset($edition['nav']) || ! $edition['nav']) return '';
        // rewrite links for urn:
        $html = $edition['nav'];
        // normalize url
        $html = preg_replace('@href="[\./]*urn:@', 'href="urn:', $html);
        // no word searched
        if (!count($formids)) {
            $html = preg_replace(
                '@ href="' . $cts . '"@',
                '$1 class="selected"',
                $html
            );
        }
        else {

            $in  = str_repeat('?,', count($formids) - 1) . '?';
            $sql = "SELECT COUNT(*) FROM tok, doc WHERE $f IN ($in) AND doc = doc.id AND cts = ?";
            $qTok =  Verbatim::$pdo->prepare($sql);
            $params = $formids;
            $i = count($params);
            // occurrences by chapter ?
            $html = preg_replace_callback(
                array(
                    '@<a href="([^"]+)">([^<]+)</a>@',
                ),
                function ($matches) use ($cts, $q, $qTok, $params, $i){
                    $params[$i] = $matches[1];
                    $qTok->execute($params);
                    list($count) = $qTok->fetch();
                    $ret = '';
                    $ret .= '<a';
                    if ($matches[1] == $cts) {
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
                $html
            );
        }
        // rewrite links for context
        if (self::win()) {
            $html = preg_replace('@href="[\./]*urn:@', 'href="../urn/', $html);
        } else {
            $html = preg_replace('@href="[\./]*urn:@', 'href="./urn:', $html);
        }
        return $html;
    }

    /**
     * A search form
     */
    public static function qform($down=false, $route='conc')
    {
        $selected = Route::match($route)?' selected':'';
        $q = Http::par('q');
        if ($q === null) $q = '';
        $radio = new Radio('f');
        $radio->add('lem', I18n::_('search.lem'));
        $radio->add('orth', I18n::_('search.form'));
        echo '
<form action="' . Route::home_href() . $route . '" class="qform' . $selected . '">
    <div class="radios">' . $radio . '    </div>
    <div  class="input">';
        if ($down) {
            echo '
        <button type="submit" title="' . I18n::_('search.indoc') . '" onclick="this.form.action=\'\'">▼</button>';
        }
        echo'
        <input name="q" class="q" value="' . htmlspecialchars($q) . '" />
        <button type="submit">▶</button>
    </div>
</form>
';    
    }

    /**
     * Build a kind of biblio record, not perfect
     */
    static public function edition(&$edition)
    {
        $line = '';
        $line .= '<span class="authors">' . $edition['authors'] . '</span>';
        $line .= ', <em class="title">' . $edition['title'] . '</em>';
        $line .= ' (';
        $line .= 'ed. <span class="editors">' . $edition['editors'] . '</span>';
        if (isset($edition['volume']) && $edition['volume']) {
            $line .= ', <span class="volume">vol. ' . $edition['volume'] . '</span>';
        }
        if (isset($editio['page_end']) && $edition['page_end']) {
            $line .= ', <span class="page">p. ' . $editio['page_start'] . '-' . $editio['page_end'] . '</span>';
        }
        else if (isset($editio['page_start']) && $edition['page_start']){
            $line .= ', <span class="page">p. ' . $edition['page_start'] . '</span>';
        }
        $line .= ')';
        return $line;
    }

    /**
     * best numerotation of a section
     */
    static public function num(&$doc)
    {
        $num = array();
        foreach (array('liber', 'capitulum', 'sectio') as $cts) {
            if (!isset($doc[$cts])) continue;
            if (!$doc[$cts]) continue;
            $num[] = $doc[$cts];
        }
        return implode('.', $num);
    }

    /**
     * Give a displayable scope (vol., p.) for a section of an edition
     */
    static public function scope(&$doc)
    {
        $line = '';
        if (isset($doc['volume']) && $doc['volume']) {
            $line .= ', <span class="volume">vol. ' . $doc['volume'] . '</span>';
        }
        /*
        else if (isset($edition['volumen']) && $edition['volumen']) {
            $line .= ', <span class="volumen">vol. ' . $edition['volumen'] . '</span>';
        }
        */
        // a bug, but could be found
       if (isset($doc['page_end']) && $doc['page_end']) {
            $line .= ', <span class="page">p. ' . $doc['page_start'] . '-' . $doc['page_end'] . '</span>';
        }
        else if (isset($doc['page_start']) && $doc['page_start']) {
            $line .= ', <span class="page">p. ' . $doc['page_start'] . '</span>';
        }
        return $line;
    }

    /**
     * All this bib, not well optimized
     */
    static public function bibl(&$edition, &$doc)
    {
        // parts
        $line = '';
        $line .= '<span class="authors">' . $edition['authors'] . '</span>';
        $line .= ', <em class="title">' . $edition['title'] . '</em>';
        $num = self::num($doc);
        if ($num) $line .= ', ' . $num;
        $line .= ', ed. <span class="editors">' . $edition['editors'] . '</span>';
        $line .= self::scope($doc);
        return $line;
    }
}
