<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING Q20 PROTOTYPE FIELD ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 prototype value: '" . $q20->prototype . "'\n";
echo "Is NULL: " . (is_null($q20->prototype) ? "YES" : "NO") . "\n";
echo "Is empty string: " . (empty($q20->prototype) ? "YES" : "NO") . "\n";

// Check what the DB structure looks like
$columns = $DB->get_columns('question_coderunner_options');
echo "\n\nPrototype column info:\n";
if (isset($columns['prototype'])) {
    $col = $columns['prototype'];
    echo "  Type: " . $col->type . "\n";
    echo "  Length: " . (isset($col->length) ? $col->length : "N/A") . "\n";
} else {
    echo "  Column NOT FOUND!\n";
}

// Actually let's look at raw SQL
$result = $DB->get_recordset_sql("SELECT questionid, prototype, coderunnertype FROM {question_coderunner_options} WHERE questionid IN (1, 16, 17, 20)");
echo "\n\nRaw data:\n";
foreach ($result as $row) {
    echo "Q{$row->questionid}: prototype='{$row->prototype}', type={$row->coderunnertype}\n";
}
?>
