<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Question Sandbox Configuration ===\n\n";

// Get all CodeRunner questions
$questions = $DB->get_records('question_coderunner_options', []);

foreach ($questions as $q) {
    $question = $DB->get_record('question', ['id' => $q->questionid]);
    echo "Question ID: {$q->questionid} - {$question->name}\n";
    echo "  - Sandbox: " . ($q->sandbox ?? 'NULL') . "\n";
    echo "  - Language: " . ($q->language ?? 'NULL') . "\n";
}

if (count($questions) === 0) {
    echo "No CodeRunner questions found!\n";
}
?>
