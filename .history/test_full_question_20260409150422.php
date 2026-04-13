<?php
define('CLI_SCRIPT', true);
require 'config.php';

global $DB, $CFG;

echo "=== Checking Addition Question ===\n\n";

$question = $DB->get_record('question', ['id' => 20]);
echo "Question ID 20: {$question->name}\n";

$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "  - Sandbox: " . ($opts->sandbox ?? 'NULL') . "\n";
echo "  - Language: " . ($opts->language ?? 'NULL') . "\n";

echo "\n=== Testing Question Loading & Sandbox ===\n";

require_once($CFG->dirroot . '/question/engine/bank.php');

try {
    $q = question_bank::load_question($question->id);
    echo "✓ Question loaded: {$q->name}\n";
    
    // Check sandbox property
    echo "  - Question sandbox: " . ($q->sandbox ?? 'NULL') . "\n";
    
    // Try to get sandbox
    $sandbox = $q->get_sandbox();
    echo "✓ Sandbox obtained: " . get_class($sandbox) . "\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
  echo $e->getTraceAsString() . "\n";
}
?>
