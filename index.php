<?php declare(strict_types=1);

/** Galenus Router */

/** Require master class  */
require_once(__DIR__ . '/Galenus.php');

use Oeuvres\Kit\{Route, I18n, Http};
use GalenusVerbatim\Verbatim\{Verbatim};


// connect to a database prepared with verbapie
// https://github.com/galenus-verbatim/verbapie
$config_file = __DIR__ . "/config.php";
if (!file_exists($config_file)) {
    echo "<h1>[Install] no_config. \$cp _config.php config.php</h1>";
    return;
}
$config = require($config_file);


Verbatim::connect($config['db']);

// Get a language to route correctly, store it for other pages
$lang = Http::par('lang', 'fr', '/en|fr/', 'lang');
// send the attribute to other consumers
Route::setAtt("lang", $lang);
// Register verbatim messages for the app
I18n::load(Verbatim::dir() . $lang . '.tsv');
// Register galenus specific messages
I18n::load(__DIR__ . '/' . $lang . '.tsv');
// register the template in which include content
Route::template(__DIR__ . '/template.php');
// check zotero rdf, generate if needed
Galenus::zotero();

// try a redirection to a Kühn reference
Route::get('/([\dIVX].*)', __DIR__ . '/pages/kuhn.php', array('kuhn' => '$1'), null);
// urn:cts redirection, see spec 
// https://www.digitalathenaeus.org/tools/KaibelText/cts_urn_retriever.php
// urn:cts:greekLit:tlg0008.tlg001.perseus-grc2:3.7
// some server may 403 on ':' in url, support '_'
Route::get(
    'urn[:_].*',
    Verbatim::dir() . 'pages/cts.php',
    array('URN' => '$0'),
    null
);
// a tlg opus
Route::get('/(tlg\d+\.tlg\d+)', Verbatim::dir() . 'pages/opus.php', array('cts' => '$1'));
// a tlg content, array to pass params extracted from url path, local page
Route::get('/(tlg.*)', __DIR__ . '/pages/doc.php', array('cts' => '$1'));

// welcome page
Route::get('/', __DIR__ . '/pages/opera.php');

// Docx is a filter without output, transform docx in html if requested
Route::get('/(.*)', __DIR__ . '/pages/docx.php');
// try if a local html page is available
Route::get('/(.*)', __DIR__ . '/html_cache/$1.html');
// try if a local tool page is available
Route::get('/(.*)', __DIR__ . '/pages/$1.php');
// try if a tool page is available on Verbatim
Route::get('/(.*)',  Verbatim::dir() . '$1.php');

// Catch all
Route::route('/404', __DIR__ . '/html_cache/404_' . $lang . '.html');
Route::route('/404', __DIR__ . '/html_cache/404.html');
// Default catch all in Verbatim
Route::route('/404', Verbatim::dir() . '/pages/404.html');
// No Route has worked
echo "Bad routage, 404.";
