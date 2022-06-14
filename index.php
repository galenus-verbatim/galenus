<?php
/** Galenus Router */
declare(strict_types=1);
/** Require master class  */
require_once(__DIR__ . '/Galenus.php');

use Oeuvres\Kit\{Route,I18n};

// connect to a database prepared with verbapie
// https://github.com/galenus-verbatim/verbapie
Verbatim::connect(Galenus::db_file());

// Get a language to route correctly

// Register messages for the app
I18n::load(require_once(Route::app_dir() .'fr.php'));


// register the template in which include content
Route::template(__DIR__ . '/template.php');
// welcome page
Route::get('/', __DIR__ . '/pages/welcome.html');
// a tlg content, array to pass params extracted from url path
Route::get('/(tlg.*)', __DIR__ . '/pages/doc.php', array('cts' => '$1'));
// try if a php content is available
Route::get('/(.*)', $verbadir . 'pages/$1.php'); 
 // try if an html content is available
Route::get('/(.*)', $verbadir . 'pages/$1.html');
// catch all
Route::route('/404', __DIR__ . '/pages/404.html');
// No Route has worked
echo "Bad routage, 404.";
