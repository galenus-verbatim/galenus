<?php declare(strict_types=1);

require_once(__DIR__ . "/Galenus.php");

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{LoggerCli};

$logger = new LoggerCli(LogLevel::DEBUG);
Galenus::setLogger($logger);
Verbatim::connect(Galenus::db_file());
Galenus::zotero();
// generate sitemap.xml ??
// require_once(dirname(__DIR__) . "/sitemap.php");
Galenus::pages(); // true = force
