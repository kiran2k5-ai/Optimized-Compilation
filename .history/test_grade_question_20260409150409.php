<?php
define('CLI_SCRIPT', true);
require 'config.php';

global $DB;

echo "=== Checking Addition Question ===\n\n";

$question = $DB->get_record('question', ['id' => 20]);
echo "Question ID 20: {$question->name}\n";

$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "  - Sandbox: " . ($opts->sandbox ?? 'NULL') . "\n";
echo "  - Language: " . ($opts->language ?? 'NULL') . "\n";

echo "\n=== Testing Question Grading ===\n";

require_once('question/engine/bank.php');

try {
    $q = question_bank::load_question($question->id);
    echo "✓ Question loaded\n";
    echo "  - Name: {$q->name}\n";
    echo "  - Sandbox setting: " . ($q->sandbox ?? 'NULL (use default)') . "\n";
    
    // Try to get sandbox
    $sandbox = $q->get_sandbox();
    echo "✓ Sandbox obtained: " . get_class($sandbox) . "\n";
    
    // Try get_languages
    $langs = $sandbox->get_languages();
    echo "✓ Languages: " . count($langs) . " supported\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
