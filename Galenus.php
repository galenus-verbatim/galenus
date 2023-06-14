<?php declare(strict_types=1);

/**
 * Part of galenus-verbatim https://github.com/galenus-verbatim/galenus
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */

/**
 * This class requires verbatim, https://github.com/galenus-verbatim/varbatim
 * Specific code for Galenus https://galenus-verbatim.huma-num.fr/
 */

declare(strict_types=1);

require_once(__DIR__ . '/vendor/autoload.php');

// require_once(dirname(__DIR__) . '/verbatim/Verbatim.php');

use Oeuvres\Kit\{Filesys, Log, Xt};
use GalenusVerbatim\Verbatim\{Verbatim};


Galenus::init();
class Galenus
{
    /** configuration parameters */
    static public $config;

    /**
     * Init static things
     */
    static public function init()
    {
        $config_file = __DIR__ . '/config.php';
        if (file_exists($config_file)) {
            self::$config = include($config_file);
        }
    }

    static function zotero_xmlnorm($rdf_file)
    {
        // wash a bit rdf before
        $xml = file_get_contents($rdf_file);
        // Extra tag
        $xml = preg_replace(
            '@</dc:description>@',
            "\n$0",
            $xml,
        );
        $xml = preg_replace_callback(
            array(
                '@(CTS URN|Original Title|French Title|English Title|English Short Title) *: *(.*)@'
            ),
            function ($matches) {
                $tag = preg_replace('/ +/', '-', trim($matches[1]));
                $tag = strtolower($tag);
                return "<z:$tag>$matches[2]</z:$tag>";
            },
            $xml
        );
        // Entities in notes
        $xml = preg_replace_callback(
            array(
                '@(<bib:Memo[^>]*>\s*<rdf:value>)([^<]*?)(</rdf:value>\s*</bib:Memo>)@',
            ),
            function ($matches) {
                // pb with double decoding
                $html = str_replace( 
                    ['&amp;lt;', '&amp;gt;', '&amp;gt;'], 
                    ['‹', '›', '＆'], 
                    $matches[2]
                );
                $html = html_entity_decode($html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XHTML);
                $html = html_entity_decode($html);
                return $matches[1] . $html . $matches[3];
            },
            $xml
        );
        return $xml;
    }

    /**
     * 
     */
    static function zotero(
        $rdf_file = __DIR__ . "/Galenus-verbatim.rdf", 
        $force = false
    ) {
        if (!file_exists($rdf_file)) {
            Log::error($rdf_file . " not found for a Zotero RDF export.");
            return;
        }
        // test freshness
        // this file knows last generation
        $editiones_file = __DIR__ . "/html_cache/editiones.html";

        if ($force) {
            Log::info('Rebuild, force');
        }
        else if (!file_exists($editiones_file)) {
            Log::info('New build, file not found ' . $editiones_file);
        } else if (filemtime($editiones_file) < filemtime($rdf_file)) {
            Log::info('Rebuild, new zotero rdf export');
        } else if (filemtime($editiones_file) + 10 < filemtime(Verbatim::db_file())) {
            Log::info('Rebuild, new verbapie database');
        } else {
            return; // OK
        }

        Log::info('Generate resources from Zotero rdf export ' . $rdf_file);
        // normalize xml of html oddiities
        $xml = self::zotero_xmlnorm($rdf_file);
        // for debug, verify xml
        file_put_contents($rdf_file .".xml", $xml); 
        $dom = Xt::loadXml($xml);
        /* editiones */
        $editiones = Xt::transformToXml(
            __DIR__ . "/build/galenzot_editiones.xsl",
            $dom
        );

        Verbatim::$pdo->beginTransaction();
        $re = '@<section id="([^"]+)"[^>]*>.*?</section>@s';
        preg_match_all($re, $editiones, $matches);

        $cts = $matches[1];
        $bibl = $matches[0];
        $sql = "UPDATE edition SET bibl = ? WHERE cts = ?;";
        $ins = Verbatim::$pdo->prepare($sql);
        $sel = Verbatim::$pdo->prepare("SELECT COUNT(*) FROM edition WHERE cts = ?;");

        for ($i = 0, $max = count($bibl); $i < $max; $i++) {
            $sel->execute(array($cts[$i]));
            list($num) = $sel->fetch();
            if (!$num) {
                Log::info('CTS not found in database: ' . $cts[$i]);
                continue;
            }
            $ins->execute(array($bibl[$i], $cts[$i]));
        }
        Verbatim::$pdo->commit();

        /* opera */
        $html = Xt::transformToXml(
            __DIR__ . "/build/galenzot_opera_navs.xsl",
            $dom
        );
        Filesys::mkdir(__DIR__ . "/html_cache/");
        file_put_contents(__DIR__ . "/html_cache/opera_navs.html", $html);

        $opera_html = Xt::transformToXml(
            __DIR__ . "/build/galenzot_opera_bib.xsl",
            $dom
        );
        file_put_contents(__DIR__ . "/html_cache/opera_bib.html", $opera_html);

        $html = Xt::transformToXml(
            __DIR__ . "/build/galenzot_checks.xsl",
            $dom
        );
        file_put_contents(__DIR__ . "/html_cache/zotero_checks.html", $html);
        // load opus records

        Log::info('Load database with Zotero records');
        Verbatim::$pdo->exec("DELETE FROM opus;");
        Verbatim::$pdo->beginTransaction();
        $re = '@<section id="([^"]+)".*?</section>@s';
        preg_match_all($re, $opera_html, $matches);
        $cts = $matches[1];
        $bibl = $matches[0];
        $sql = "INSERT INTO opus (cts, bibl) VALUES (?, ?);";
        $insOpus = Verbatim::$pdo->prepare($sql);
        for ($i = 0, $max = count($bibl); $i < $max; $i++) {

            $insOpus->execute(array($cts[$i], $bibl[$i]));
        }
        Verbatim::$pdo->commit();
        Log::info('Database, optimize');
        Verbatim::$pdo->exec("PRAGMA auto_vacuum = FULL");
        // generate sitemap.xml
        self::sitemap();
        // finish with that, will be the timestamp
        Filesys::mkdir(dirname($editiones_file));
        file_put_contents($editiones_file, $editiones);

        Log::info('End');
    }

    static function sitemap($sitemap_file = __DIR__ . '/sitemap.xml')
    {
        $stream = fopen($sitemap_file, "w");
        $url_format = self::$config['url.doc'];
        fwrite($stream, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        fwrite($stream, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
        $stmt = Verbatim::$pdo->prepare("SELECT cts FROM doc;");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fwrite($stream, 
                '  <url><loc>'
                . sprintf($url_format, $row['cts'])
                . '</loc></url>' . "\n"
            );
        }
        fwrite($stream, '</urlset>' . "\n");
    }
}
