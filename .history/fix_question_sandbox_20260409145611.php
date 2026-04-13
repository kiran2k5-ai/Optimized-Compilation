<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Fixing Question Sandbox Configuration ===\n\n";

// Update questions with sandbox='jobe' to 'jobesandbox'
$questions = $DB->get_records('question_coderunner_options', ['sandbox' => 'jobe']);

if (count($questions) > 0) {
    foreach ($questions as $q) {
        $q->sandbox = 'jobesandbox';
        $DB->update_record('question_coderunner_options', $q);
        $question = $DB->get_record('question', ['id' => $q->questionid]);
        echo "✓ Updated Question: {$question->name}\n";
        echo "  - Changed sandbox from 'jobe' to 'jobesandbox'\n\n";
    }
    echo "Total questions fixed: " . count($questions) . "\n";
} else {
    echo "No questions with sandbox='jobe' found.\n";
}

echo "\nPurging caches...\n";
// Purge caches
purge_all_caches();
echo "✓ Caches purged!\n";
?>
