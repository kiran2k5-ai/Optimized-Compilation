<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== QUESTION_CODERUNNER_TESTS TABLE STRUCTURE ===\n\n";

$columns = $DB->get_columns('question_coderunner_tests');
foreach ($columns as $col) {
    echo "- {$col->name} ({$col->type})\n";
}

echo "\n\n=== Q20 TESTS ===\n";
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20], 'id ASC');
foreach ($tests as $test) {
    echo "\nTest ID {$test->id}:\n";
    echo json_encode($test, JSON_PRETTY_PRINT);
}
?>
