<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING QUESTION 20 DIRECTLY ===\n";

// Check raw question table
$q20 = $DB->get_record('question', ['id' => 20]);
if ($q20) {
    echo "Question 20 found: {$q20->name} ({$q20->qtype})\n";
}

// Check if qtype_coderunner_options table exists
$tables = $DB->get_tables();
echo "\nSearching for CodeRunner tables:\n";
foreach ($tables as $t) {
    if (strpos($t, 'coderunner') !== false) {
        echo "  Found: {$t}\n";
    }
}

// Try direct SQL query to check CodeRunner options
echo "\nDirect SQL check for CodeRunner options:\n";
$sql = "SELECT * FROM mdl_qtype_coderunner_options WHERE questionid = 20";
try {
    $result = $DB->get_records_sql($sql);
    if ($result) {
        echo "  Found " . count($result) . " records\n";
        foreach ($result as $r) {
            echo "    Sandbox: " . ($r->sandbox ?? 'NULL') . "\n";
            echo "    Language: " . ($r->language ?? 'NULL') . "\n";
        }
    } else {
        echo "  No records found\n";
    }
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Check question_hints
echo "\nChecking question hints/options:\n";
$hints = $DB->get_records('question_hints', ['questionid' => 20]);
echo "  Hints count: " . count($hints) . "\n";

// Get all fields from question  
echo "\nAll question 20 fields:\n";
foreach ((array)$q20 as $k => $v) {
    echo "  $k: " . (is_null($v) ? 'NULL' : (strlen($v) > 50 ? substr($v, 0, 50) . '...' : $v)) . "\n";
}
?>
