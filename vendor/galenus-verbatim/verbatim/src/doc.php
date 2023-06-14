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
        // hack for Apache on windows with ':'
        $cts = str_replace('urn/', 'urn:', $cts);
        self::$cts = $cts;
        // take the first doc starting with this cts
        $sql = "SELECT * FROM doc WHERE cts LIKE ? LIMIT 1";
        $qDoc = Verbatim::$pdo->prepare($sql);
        $qDoc->execute(array($cts. '%'));
        self::$doc = $qDoc->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT * FROM editio WHERE id = ? LIMIT 1";
        $qed = Verbatim::$pdo->prepare($sql);
        $qed->execute([self::$doc['editio']]);
        self::$editio = $qed->fetch(PDO::FETCH_ASSOC);
    }
}
Data::init();

/**
 * Called by template to give a contextual <title>
 */
$title = function() {
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc || !$editio) return null;
    $s = '';
    $s .= $editio['auctor'];
    $s .= '. ' .$editio['titulus'];
    $num = Verbatim::num($doc);
    if ($num) $s .= ', ' . $num;
    $s .= ', ed. ' . $editio['editor'];
    $s .= Verbatim::scope($doc);
    $s .= '.  ' . $doc['cts'];
    $s .= ' — ' . Verbatim::name();
    $s = strip_tags($s);
    return $s;
};

/**
 * Called by template to give main content
 */
$main = function() {
    $cts = Data::$cts;
    $doc = Data::$doc;
    $editio = Data::$editio;
    if (!$doc) {
        http_response_code(404);
        echo I18n::_('doc.notfound', Data::$cts);
        return;
    }
    $q = Http::par('q');
    $cts = $doc['cts'];

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
};

?>

