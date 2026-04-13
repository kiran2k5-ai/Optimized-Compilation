<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Looking for Quiz-Question Association ===\n\n";

// Check what tables exist
$tables = $DB->get_tables();
$quiz_related = array_filter($tables, function($t) { return strpos($t, 'quiz') !== false; });
echo "Quiz-related tables:\n";
foreach ($quiz_related as $table) {
    echo "  - $table\n";
}

echo "\n=== Checking quiz Table ===\n";
$columns = $DB->get_columns('quiz');
foreach ($columns as $col => $details) {
    echo "  - {$col}\n";
}

echo "\n=== Checking question Table ===\n";
$columns = $DB->get_columns('question');
foreach (array_keys($columns) as $col) {
    echo "  - {$col}\n";
}

echo "\n=== Looking for Question Links ===\n";

// Check if there's a question_references table
if (in_array('question_references', $tables)) {
    echo "question_references table exists\n";
    $refs = $DB->get_records_sql("SELECT * FROM {question_references} LIMIT 5");
    foreach ($refs as $ref) {
        echo "  - " . json_encode((array)$ref) . "\n";
    }
}

// Check if there's a question_bank_entries table
if (in_array('question_bank_entries', $tables)) {
    echo "\nquestion_bank_entries table exists\n";
    $entries = $DB->get_records_sql("SELECT * FROM {question_bank_entries} LIMIT 5");
    foreach ($entries as $entry) {
        echo "  - Entry: {$entry->id}, Question: {$entry->questionid}\n";
    }
}
?>
