<?php
declare(strict_types=1);

require_once(__DIR__ . "/Verbatim.php");

use Oeuvres\Kit\{I18n,Http};
use GalenusVerbatim\Verbatim\{Verbatim};

class Data {
    /** requested cts */
    public static $cts;
    /** Doc record from database */
    public static $doc;
    /** Editio record from database */
    public static $editio;
    /** init param */
    public static function init() {
        $cts = Http::par('cts');
        self::$cts = $cts;
        if (strpos($cts, '_') === false) { // cover
            $sql = "SELECT * FROM doc WHERE clavis LIKE ? LIMIT 1";
            $qDoc = Verbatim::$pdo->prepare($sql);
            $qDoc->execute(array($cts . '%'));
        }
        else { // should be a document
            $sql = "SELECT * FROM doc WHERE clavis LIKE ? LIMIT 1";
            $qDoc = Verbatim::$pdo->prepare($sql);
            $qDoc->execute(array($cts. '%'));
        }
        self::$doc = $qDoc->fetch(PDO::FETCH_ASSOC);
        
        $edclavis = strtok($cts, '_');
        $sql = "SELECT * FROM editio WHERE clavis = ? LIMIT 1";
        $qed = Verbatim::$pdo->prepare($sql);
        $qed->execute(array($edclavis));
        self::$editio = $qed->fetch(PDO::FETCH_ASSOC);
    }
}
Data::init();

/**
 * Called by template to give a contextual <title>
 */
function title() {
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc || !$editio) return null;
    $title = '';
    $title .= $editio['auctor'];
    $title .= '. ' .$editio['titulus'];
    $num = Verbatim::num($doc);
    if ($num) $title .= ', ' . $num;
    $title .= ', ed. ' . $editio['editor'];
    $title .= Verbatim::scope($doc);
    $title .= '. urn:cts:greekLit:' . preg_replace('@_@', ':', $doc['clavis']);
    $title .= ' — ' . Verbatim::name();
    $title = strip_tags($title);
    return $title;
}

/**
 * Called by template to give main content
 */
function main() {
    $cts = Data::$cts;
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', Data::$cts);
        return;
    }
    $q = Http::par('q');
    $clavis = $doc['clavis'];

    // Get existing formids (int) from a free query
    $forms = array();
    if ($q) {
        $field = Http::par('f', 'lem', '/lem|orth/');
        $forms = Verbatim::forms($q, $field);
    }
    $formids = array_keys($forms); // array of ints
// html
?>
<div class="reader">
    <div class="toc">
        <?= Verbatim::nav($editio, $doc, $formids) ?>
    </div>
    <div class="doc">
        <main>
            <header class="doc">
                <?= Verbatim::ante($doc) ?>
                <div>
                    <?= Verbatim::bibl($editio, $doc) ?>
                </div>
                <?= Verbatim::post($doc) ?>
            </header>
            <div class="doc">
                <?= Verbatim::hidoc($doc, $formids) ?>
            </div>
            <footer class="doc">
                <?= Verbatim::ante($doc) ?>
                <?= Verbatim::post($doc) ?>
            </footer>
        </main>
    </div>
</div>
<?php // close function
}
?>

