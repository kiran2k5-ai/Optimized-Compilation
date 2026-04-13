<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING question_coderunner_options TABLE SCHEMA ===\n";
$columns = $DB->get_columns('question_coderunner_options');

foreach ($columns as $col) {
    echo "{$col->name}: {$col->type}" . ($col->primary_key ? " (pk)" : "") . "\n";
}

echo "\n=== CHECKING QUESTION 20 OPTIONS RECORD ===\n";
$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
if ($opts) {
    foreach ((array)$opts as $k => $v) {
        if ($k === 'qtype') {
            echo "$k: " . var_export($v, true) . " <- THIS MIGHT BE THE PROBLEM!\n";
        }
    }
}

echo "\n=== CHECKING IF OPTIONS HAS A 'qtype' PROPERTY ===\n";
if (isset($opts->qtype)) {
    echo "YES - options->qtype exists: " . var_export($opts->qtype, true) . "\n";
} else {
    echo "NO - options->qtype does NOT exist\n";
}
?>
