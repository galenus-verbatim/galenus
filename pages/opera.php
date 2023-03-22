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
$pres_file = $html_dir . '/opera_' . $lang .'.html';
if (file_exists($pres_file)) readfile($pres_file); 
?>
<?php readfile($html_dir . '/opera_bib.html') ?>
            </main>
        </div>
        <aside id="navs">
            <?php 
$select = new Select('selnav', 'selnav', 'selnav');
$select->add('TitLa', I18n::_('zotero.TitLa'));
$select->add('fichtner', I18n::_('zotero.fichtner'));
$select->add('kuhn', I18n::_('zotero.kuhn'));
$select->add('AbbrLa', I18n::_('zotero.AbbrLa'));
$select->add('TitGrcCMG', I18n::_('zotero.TitGrcCMG'));
$select->add('TitFrBM', I18n::_('zotero.TitFrBM'));
$select->add('TitEnCGT', I18n::_('zotero.TitEnCGT'));
$select->add('AbbrEnCGT', I18n::_('zotero.AbbrEnCGT'));
echo $select;
            ?>
<?php readfile($html_dir . '/opera_navs.html') ?>
            
        </aside>

    </div>

    <?php
}