<?php
/** Galenus Router */
declare(strict_types=1);
/** Require master class  */
require_once(dirname(__DIR__) . '/verbatim/php/autoload.php');

use Oeuvres\Kit\{Route,I18n};

echo "<h1>Coucou</h1>";
// connect to a database prepared with verbapie
// https://github.com/galenus-verbatim/verbapie
// Verbatim::connect($verbadir . 'galenus.db');

// Get a language

// Register messages for the app
I18n::load(require_once(Route::app_dir() .'fr.php'));

/*
// register the template in which include content
Route::template($verbadir . 'template.php');
// welcome page
Route::get('/', $verbadir . 'pages/welcome.html');
// a tlg content, array to pass params extracted from url path
Route::get('/(tlg.*)', $verbadir . 'pages/doc.php', array('cts' => '$1'));
// try if a php content is available
Route::get('/(.*)', $verbadir . 'pages/$1.php'); 
 // try if an html content is available
Route::get('/(.*)', $verbadir . 'pages/$1.html');
// catch all
Route::route('/404', $verbadir . 'pages/404.html');
// No Route has worked
echo "Bad routage, 404.";
*/