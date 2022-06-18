<?php
declare(strict_types=1);

require_once(__DIR__ . "/Galenus.php");

use Oeuvres\Kit\{I18n, Route, Web};


$page = ltrim(Route::request_path(), '/');
$body_class = $page;
if (@substr_compare($page, 'tlg', 0, strlen('tlg'))==0) {
    $body_class = 'tlg';
}
$cts = Web::par('kuhn', '');
$lang = Route::lang();

?><!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title><?= Route::title('Galenus Verbatim') ?></title>
        <link  href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" rel="stylesheet"/>
        <link rel="stylesheet" href="<?= Route::res_href('../verbatim/verbatim.css') ?>"/>
        <link rel="stylesheet" href="<?= Route::res_href('theme/galenus.css') ?>"/>
    </head>
    <body class="<?=$body_class?>">
<div id="all">
    <header id="header">
        <div class="banner">
            <div class="titles">
                <a href="<?= Route::home_href() ?>.">
                    <div class="title">Galenus verbatim</div>
                    <div class="titlesub">Γαληνὸς κατὰ λέξιν</div>
                </a>
            </div>
            <div class="moto"><?= I18n::_('template.moto') ?></div>
            <img class="banner" src="<?= Route::res_href('theme/galenus-verbatim.jpg') ?>" />
        </div>
    </header>
    <div id="content">
        <nav id="tabs" class="tabs">
            <form action="" onsubmit="this.action = encodeURIComponent(this.cts.value.replaceAll(':', '_'));">
                <label for="cts"><?= I18n::_('cts.label') ?></label>
                <input  id="cts" name="cts"
                    title="<?= I18n::_('cts.title') ?>" 
                    placeholder="<?= I18n::_('cts.placeholder') ?>" 
                    value="<?= htmlspecialchars($cts) ?>"
                />
            </form>
            <?= Route::tab('', I18n::_('template.opera')) ?>
            <?php 
            if ($page == 'tlg') {
                // if doc visible, add a buttoon search in doc search in doc
                Verbatim::qform(true);
            }
            else {
                Verbatim::qform();
            }
            
            ?>
            <?= Route::tab(I18n::_('template.de_href'), I18n::_('template.de')) ?>
        </nav>
        <div class="container">
            <?php Route::main(); ?>
        </div>
    </div>
    <footer id="footer">
        <nav id="logos">
            <a href="https://www.iufrance.fr/" title="Institut universitaire de France"><img alt="Institut Universitaire de France" src="<?=  Route::res_href('theme/logo_IUF.png') ?>"/></a>

            <a href="http://www.orient-mediterranee.com/spip.php?rubrique314" title="UMR 8167 Orient et Méditerranée"><img alt="UMR 8167 Orient et Méditerranée" src="<?=  Route::res_href('theme/logo_UMR8167.png') ?>"/></a>

            <a href="https://lettres.sorbonne-universite.fr/faculte-des-lettres/ufr/lettres/grec/" title="Faculté des Lettres de Sorbonne Université"><img alt="Faculté des Lettres de Sorbonne Université" src="<?=  Route::res_href('theme/logo_sorbonne-lettres.png') ?>"/></a>



            <a href="https://humanites-biomedicales.sorbonne-universite.fr/" title="Initiative humanités biomédicales de l’Alliance Sorbonne Université"><img alt="Initiative humanités biomédicales de l’Alliance Sorbonne Université" src="<?=  Route::res_href('theme/logo_humabiomed.png') ?>"/></a>

            <a href="#" onmouseover="this.href='ma'+'i'+'lto:'+'etymologika' + '\u0040gm' + 'ail.com';"><img style="opacity: 0.7;" src="<?=  Route::res_href('theme/enveloppe.png') ?>"/></a>
        </nav>
    </footer>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
        <script src="<?= Route::res_href('theme/galenus.js') ?>"></script>
    </body>
</html>
