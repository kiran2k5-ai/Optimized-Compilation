<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING TEST CASES FOR QUESTION 20 ===\n";

$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20]);

if (count($tests) === 0) {
    echo "No test cases found!\n";
} else {
    echo "Found " . count($tests) . " test cases:\n\n";
    
    foreach ($tests as $t) {
        echo "Test ID: {$t->id}\n";
        echo "  Input: " . substr($t->input ?? '', 0, 100) . "\n";
        echo "  Expected: " . substr($t->expected ?? '', 0, 100) . "\n";
        echo "  Display: {$t->display}\n";
        echo "  Mark: {$t->mark}\n";
        echo "\n";
    }
}
?>
