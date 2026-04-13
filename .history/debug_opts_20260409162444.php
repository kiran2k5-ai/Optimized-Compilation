<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING question_coderunner_options TABLE ===\n";

// Check the actual table structure and data
$sql = "SELECT * FROM mdl_question_coderunner_options WHERE questionid = 20";
$result = $DB->get_records_sql($sql);
if ($result) {
    echo "Found " . count($result) . " records for question 20:\n";
    foreach ($result as $row) {
        echo "\n--- Record ---\n";
        foreach ((array)$row as $k => $v) {
            echo "$k: " . (is_null($v) ? 'NULL' : (strlen($v) > 100 ? substr($v, 0, 100) . '...' : $v)) . "\n";
        }
    }
} else {
    echo "No records found for question 20!\n";
}

// Check all questions with CodeRunner options
echo "\n\n=== ALL CODERUNNER QUESTIONS ===\n";
$sql = "SELECT questionid, sandbox, language FROM mdl_question_coderunner_options LIMIT 5";
$all = $DB->get_records_sql($sql);
foreach ($all as $row) {
    echo "Q{$row->questionid}: sandbox='{$row->sandbox}' language='{$row->language}'\n";
}
?>
