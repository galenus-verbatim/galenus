<?php declare(strict_types=1);

require_once(dirname(__DIR__) . "/Galenus.php");

use Psr\Log\LogLevel;
use Oeuvres\Kit\{Http, Log, LoggerWeb, Route, Xt};
use Oeuvres\Teinte\Format\{Docx};
use Oeuvres\Xsl\{Xpack};
/**
 * This filter will always return false, to let the Route continue
 * Requested html page will be updated if needed.
 */


// testing if there is a docx, test date
Log::setLogger(new LoggerWeb(LogLevel::DEBUG));
// a caller 
if (!isset($docx_file)) {
    $name = Http::par('page', trim(Route::url_request(), '/\\'));
    $docx_file = dirname(__DIR__) . "/docx/" . $name . ".docx";
}
if (!file_exists($docx_file)) {
    return false;
}
$name = pathinfo($docx_file, PATHINFO_FILENAME);
$html_file = dirname(__DIR__) . "/html_cache/" . $name . ".html";
$force = Http::par('force', null);

// go out ?
if (
    !$force
    && file_exists($html_file)
    && filemtime($html_file) > filemtime($docx_file)
) return false;


$docx = new Docx();
$docx->load($docx_file);
$docx->tei();
$xsl_file = Xpack::dir() . 'tei_html_article.xsl';
Xt::transformToUri(
    $xsl_file,
    $docx->dom(),
    $html_file
);
return false;