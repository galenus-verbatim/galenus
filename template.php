<?php declare(strict_types=1);

require_once(__DIR__ . "/Galenus.php");

use Oeuvres\Kit\{Http, I18n, Route};
use GalenusVerbatim\Verbatim\{Verbatim};


$page = ltrim(Route::url_request(), '/');
$body_class = $page;
if (strpos($page, 'cts:') !== false) {
    $body_class = 'cts';
}
$cts = Http::par('kuhn', '');
$lang = Route::getAtt("lang");

?><!doctype html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title><?= Route::title('Galenus Verbatim') ?></title>
        <link rel="icon" href="data:;base64,iVBORw0KGgo="/>
        <link  href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" rel="stylesheet"/>
        <link rel="stylesheet" type="text/css" href="<?= Route::home_href() ?>theme/teinte.css" />
        <link rel="stylesheet" type="text/css" href="<?= Route::home_href() ?>theme/teinte.tree.css" />
        <link rel="stylesheet" href="<?= Route::home_href() ?>vendor/galenus-verbatim/verbatim/verbatim.css"/>
        <link rel="stylesheet" href="<?= Route::home_href() ?>theme/galenus.css"/>
    </head>
    <body class="<?=$body_class?>">
<header id="header"  class="nav-down">
    <div class="banner">
        <div class="titles">
            <a href="<?= Route::home_href() ?>.">
                <div class="title">Galenus verbatim</div>
                <div class="titlesub">Γαληνὸς κατὰ λέξιν</div>
            </a>
        </div>
        <div class="moto"><?= I18n::_('template.moto') ?></div>
        <img class="banner" src="<?= Route::home_href() ?>theme/galenus-verbatim.jpg" />
    </div>
</header>
<div id="all">
    <div id="content">
        <nav id="tabs" class="tabs">
            <span class="tab">Galenus<br/>Verbatim</span>
            <form action="" onsubmit="this.action = encodeURIComponent(this.cts.value.replaceAll(':', '_'));">
                <label for="cts"><?= I18n::_('cts.label') ?></label>
                <br/>
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
            // if in concordantia, keep it
            else if ($page == 'concordantia') {
                Verbatim::qform(false,  'concordantia');
            }
            else {
                Verbatim::qform();
            }
            
            ?>
            <?= Route::tab(I18n::_('template.de_href'), I18n::_('template.de')) ?>
            <?= Route::tab('novitates.fr', 'Actualités') ?>

            <a class="tab zotero" target="_blank" rel="noopener" href="https://www.zotero.org/groups/4571007/galenus-verbatim/library">
                <span>Ad
                    <br/>bibliothecam</span>
                <img height="40px" src="<?= Route::home_href() ?>theme/logo_zotero.png"/>
            </a>
        </nav>
        <div class="container">
            <?php Route::main(); ?>
        </div>
    </div>
    <footer id="footer">
        <nav id="logos">
            <a href="https://www.iufrance.fr/" title="Institut universitaire de France"><img alt="Institut Universitaire de France" src="<?= Route::home_href() ?>theme/logo_IUF.png"/></a>

            <a href="http://www.orient-mediterranee.com/spip.php?rubrique314" title="UMR 8167 Orient et Méditerranée"><img alt="UMR 8167 Orient et Méditerranée" src="<?= Route::home_href() ?>theme/logo_UMR8167.png"/></a>

            <a href="https://lettres.sorbonne-universite.fr/faculte-des-lettres/ufr/lettres/grec/" title="Faculté des Lettres de Sorbonne Université"><img alt="Faculté des Lettres de Sorbonne Université" src="<?= Route::home_href() ?>theme/logo_sorbonne-lettres.png"/></a>

            <a href="https://humanites-biomedicales.sorbonne-universite.fr/" title="Initiative humanités biomédicales de l’Alliance Sorbonne Université"><img alt="Initiative humanités biomédicales de l’Alliance Sorbonne Université" src="<?= Route::home_href() ?>theme/logo_humabiomed.png"/></a>

            <a href="https://documentation.huma-num.fr/hebergement-web/" title="Site hébergé par Huma-Num"><img alt="Site hébergé par Huma-Num" src="<?= Route::home_href() ?>theme/logo_hn.png"/></a>

            <a href="#" onmouseover="this.href='ma'+'i'+'lto:'+'etymologika' + '\u0040gm' + 'ail.com';"><img style="opacity: 0.7;" src="<?= Route::home_href() ?>theme/enveloppe.png"/></a>
        </nav>
    </footer>
</div>
        <script type="text/javascript" charset="utf-8" src="<?= Route::home_href() ?>theme/teinte.tree.js">//</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
        <script src="<?= Route::home_href() ?>theme/galenus.js"></script>
    </body>
</html>
