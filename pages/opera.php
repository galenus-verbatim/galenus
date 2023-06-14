<?php declare(strict_types=1);
/**
 * Part of verbatim https://github.com/galenus-verbatim/verbatim
 * Copyright (c) 2021 Nathalie Rousseau
 * MIT License https://opensource.org/licenses/mit-license.php
 */
require_once(dirname(__DIR__) . "/Galenus.php");

use Oeuvres\Kit\{I18n, Route, Select};

function title() {
    return I18n::_('title');
}

function main() {
    $lang = Route::getAtt("lang");
    $html_dir = dirname(__DIR__) . '/html_cache/';
    ?>
    <div id="opera" class="opera">
        <div id="biblio" class="biblio">
            <main>
<?php
$name = 'opera_' . $lang;
$pres_file = $html_dir . $name .'.html';
// ensure freshness
$docx_file =  dirname(__DIR__) . '/docx/' . $name . '.docx';
include __DIR__ . '/docx.php';
if (file_exists($pres_file)) {
    readfile($pres_file);
}
?>
<?php 
$html = file_get_contents($html_dir . '/opera_bib.html');
// for dev on windows, rewrite url with ':'
if (Galenus::$config['win']) {
    $html = preg_replace('@./urn:@', './urn/', $html);
}

echo $html;
?>
            </main>
        </div>
        <aside id="navs">
            <?php 
$select = new Select('selnav', 'selnav', 'selnav');
$select->add('titLat', I18n::_('zotero.titLat'));
$select->add('fichtner', I18n::_('zotero.fichtner'));
$select->add('kuhn', I18n::_('zotero.kuhn'));
$select->add('titLatAbbr', I18n::_('zotero.titLatAbbr'));
$select->add('titGrc', I18n::_('zotero.titGrc'));
$select->add('titFra', I18n::_('zotero.titFra'));
$select->add('titEng', I18n::_('zotero.titEng'));
$select->add('titEngAbbr', I18n::_('zotero.titEngAbbr'));
echo $select;
            ?>
<?php readfile($html_dir . '/opera_navs.html') ?>
            
        </aside>

    </div>

    <?php
}