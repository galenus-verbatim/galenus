<?php

/** Galenus Router */

declare(strict_types=1);
/** Require master class  */
require_once(__DIR__ . '/Galenus.php');

use Oeuvres\Kit\{Route, I18n};

// connect to a database prepared with verbapie
// https://github.com/galenus-verbatim/verbapie
Verbatim::connect(Galenus::db_file());

// Get a language to route correctly, store it for other pages
$lang = Web::par('lang', 'fr', '/en|fr/', 'lang');
Route::lang($lang);
// Register verbatim messages for the app
I18n::load(Route::app_dir() . $lang . '.tsv');
// Register galenus specific messages
I18n::load(__DIR__ . '/' . $lang . '.tsv');

// register the template in which include content
Route::template(__DIR__ . '/template.php');

// try a redirection to a Kühn reference
Route::get('/([\dIVX].*)', __DIR__ . '/pages/kuhn.php', array('kuhn' => '$1'), null);
// urn:cts redirection, see spec 
// https://www.digitalathenaeus.org/tools/KaibelText/cts_urn_retriever.php
// urn:cts:greekLit:tlg0008.tlg001.perseus-grc2:3.7
// some server may 403 on ':' in url, support '_'
Route::get(
    'urn[:_].*',
    Route::app_dir() . 'pages/cts.php',
    array('URN' => '$0'),
    null
);
// a tlg opus
Route::get('/(tlg\d+\.tlg\d+)', Route::app_dir() . 'pages/opus.php', array('cts' => '$1'));
// a tlg content, array to pass params extracted from url path, local page
Route::get('/(tlg.*)', __DIR__ . '/pages/doc.php', array('cts' => '$1'));

// welcome page
Route::get('/', __DIR__ . '/pages/opera.php');

// try if a local html page is available
Route::get('/(.*)', __DIR__ . '/html/$1.html');
// try if a local tool page is available
Route::get('/(.*)', __DIR__ . '/pages/$1.pages');
// try if a tool page is available on Verbatim
Route::get('/(.*)',  Route::app_dir() . 'pages/$1.php');

// Catch all
Route::route('/404', __DIR__ . '/html/404_' . $lang . '.html');
Route::route('/404', __DIR__ . '/html/404.html');
// Default catch all in Verbatim
Route::route('/404', Route::app_dir() . '/pages/404.html');
// No Route has worked
echo "Bad routage, 404.";
