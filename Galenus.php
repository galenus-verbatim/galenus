<?php

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

require_once(dirname(__DIR__) . '/verbatim/Verbatim.php');

use Oeuvres\Kit\{Xml};
use Oeuvres\Odette\{OdtChain};
use Psr\Log\{LoggerInterface, NullLogger};

Galenus::init();
class Galenus
{
    /** Logger */
    private static $logger;
    /** The sqlite file, hard coded */
    private static $db_file = __DIR__ . '/galenus.db';
    /**
     * Set logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        // default logger
        self::$logger = $logger;
    }

    /**
     * Init static things
     */
    static public function init()
    {
        self::$logger = new NullLogger();
    }

    /**
     * Path of sqlite file
     */
    static public function db_file()
    {
        return self::$db_file;
    }

    /**
     * Update from odt
     */
    static public function pages($force = false)
    {
        $odt_dir = __DIR__ . '/odt/';
        $dst_dir = __DIR__ . '/pages/';
        $reflector = new \ReflectionClass('Oeuvres\Teinte\Teinte');
        $teinte_dir = dirname($reflector->getFileName());
        foreach (glob($odt_dir . '*.odt') as $odt_file) {
            $name = pathinfo($odt_file, PATHINFO_FILENAME);
            $html_file = $dst_dir . $name . '.html';
            // freshness ?
            if ($force);
            else if (!file_exists($html_file)); 
            else  if (filemtime($html_file) > filemtime($odt_file)) continue;

            $odt = new OdtChain($odt_file);
            // dst path is needed for images, but optimizatoin should be possible with dom
            $tei_file =  $dst_dir . $name . '.xml';
            $odt->save($tei_file);
            $tei_dom = Xml::load($tei_file);
            unlink($tei_file);

            $xsl_file =$teinte_dir . '/tei_html_article.xsl';
            Xml::transformToUri(
                $xsl_file,
                $tei_dom,
                $html_file
            );
            self::$logger->info($odt_file . ' ->- ' . $html_file);
        }
    }

    /**
     * 
     */
    static function zotero($rdf_file = __DIR__ . "/galenus-verbatim.rdf")
    {
        Xml::setLogger(self::$logger);
        // wash a bit rdf before
        $xml = file_get_contents($rdf_file);
        $xml = preg_replace(
            array(
                '@<rdf:value>&lt;p&gt;(\d\w+:.*)&lt;/p&gt;</rdf:value>@',
            ), 
            array(
                '<rdf:value>$1</rdf:value>'
            ), 
            $xml
        );
        file_put_contents($rdf_file, $xml); // record the wased rdf

        $dom = Xml::loadXml($xml);
        
        
        /* editiones */
        $editiones = Xml::transformToXml(
            __DIR__ . "/build/galenzot_editiones.xsl",
            $dom
        );
        file_put_contents(__DIR__ . "/pages/editiones.html", $editiones);


        Verbatim::$pdo->beginTransaction();
        $re = '@<section class="verbatim" id="([^"]+)">.*?</section>@s';
        preg_match_all($re, $editiones, $matches);

        $clavis = $matches[1];
        $bibl = $matches[0];
        $sql = "UPDATE editio SET bibl = ? WHERE clavis = ?;";
        $ins = Verbatim::$pdo->prepare($sql);
        $sel = Verbatim::$pdo->prepare("SELECT COUNT(*) FROM editio WHERE clavis = ?;");

        for ($i = 0, $max = count($bibl); $i < $max; $i++) {
            $sel->execute(array($clavis[$i]));
            list($num) = $sel->fetch();
            if (!$num) continue;
            $ins->execute(array($bibl[$i], $clavis[$i]));
        }
        Verbatim::$pdo->commit();

        /* opera */
        $html = Xml::transformToXml(
            __DIR__ . "/build/galenzot_opera_navs.xsl",
            $dom
        );
        file_put_contents(__DIR__ . "/pages/opera_navs.html", $html);

        $html = Xml::transformToXml(
            __DIR__ . "/build/galenzot_opera_bib.xsl",
            $dom
        );
        file_put_contents(__DIR__ . "/pages/opera_bib.html", $html);

        // load opus records

        Verbatim::$pdo->exec("DELETE FROM opus;");
        Verbatim::$pdo->beginTransaction();
        $re = '@<section class="opus" id="([^"]+)">.*?</section>@s';
        preg_match_all($re, $html, $matches);
        $clavis = $matches[1];
        $bibl = $matches[0];
        $sql = "INSERT INTO opus (clavis, bibl) VALUES (?, ?);";
        $insOpus = Verbatim::$pdo->prepare($sql);
        for ($i = 0, $max = count($bibl); $i < $max; $i++) {
            $insOpus->execute(array($clavis[$i], $bibl[$i]));
        }
        Verbatim::$pdo->commit();
    }
}
