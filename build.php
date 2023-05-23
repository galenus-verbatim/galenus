<?php declare(strict_types=1);

require_once(__DIR__ . "/Galenus.php");

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{Log, LoggerCli};
use GalenusVerbatim\Verbatim\{Verbatim};

$config = require(__DIR__ . "/config.php");
Log::setLogger(new LoggerCli(LogLevel::DEBUG));
Verbatim::connect($config['db']);
Galenus::zotero(__DIR__ . "/Galenus-verbatim.rdf", true);
// generate sitemap.xml ??
// require_once(dirname(__DIR__) . "/sitemap.php");
// Galenus::pages(); // true = force
