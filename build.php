<?php

require_once(__DIR__ . "/Galenus.php");

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{LoggerCli};

$logger = new LoggerCli(LogLevel::DEBUG);
Galenus::setLogger($logger);
// Verbatim::connect(Galenus::db_file());
// Galenus::zotero();
// generate sitemap.xml ??
// require_once(dirname(__DIR__) . "/sitemap.php");
// generate odt pages
Galenus::pages();
