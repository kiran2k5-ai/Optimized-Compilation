<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== QUESTION_CODERUNNER_OPTIONS TABLE COLUMNS ===\n\n";

$columns = $DB->get_columns('question_coderunner_options');
foreach ($columns as $col) {
    echo "- {$col->name} ({$col->type})\n";
}

echo "\n\n=== Q20 FULL RECORD ===\n";
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20], '*');
echo json_encode($q20, JSON_PRETTY_PRINT);
?>
