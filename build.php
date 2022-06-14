<?php
require_once(__DIR__ . "/Galenus.php");
Verbatim::connect(Galenus::db_file());
Galenus::zotero();
// generate sitemap.xml
// require_once(dirname(__DIR__) . "/sitemap.php");
