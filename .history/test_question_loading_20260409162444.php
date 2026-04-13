<?php
define('CLI_SCRIPT', true);
require 'config.php';

global $DB;

echo "=== Checking Addition Question Details ===\n\n";

$question = $DB->get_record('question', ['id' => 20]);
echo "Question ID 20:\n";
echo "  - Name: {$question->name}\n";
echo "  - Type: {$question->qtype}\n";

$coderunner_opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
if ($coderunner_opts) {
    echo "\nCodeRunner Options:\n";
    echo "  - Sandbox: " . (($coderunner_opts->sandbox !== null && $coderunner_opts->sandbox !== '') ? $coderunner_opts->sandbox : 'NULL (will use default)') . "\n";
    echo "  - Language: " . (($coderunner_opts->language !== null && $coderunner_opts->language !== '') ? $coderunner_opts->language : 'NULL') . "\n";
    echo "  - Grader: " . (($coderunner_opts->grader !== null && $coderunner_opts->grader !== '') ? $coderunner_opts->grader : 'NULL') . "\n";
}

echo "\n=== Loading Question via Moodle ===\n";

require_once('public/question/type/lib.php');
require_once('public/question/type/coderunner/question.php');

try {
    $q = question_bank::load_question($question->id);
    echo "✓ Loaded question: " . $q->name . "\n";
    echo "  - Type: " . $q->qtype . "\n";
    echo "  - Sandbox: " . ($q->sandbox ?? 'NULL') . "\n";
    echo "  - Language: " . ($q->language ?? 'NULL') . "\n";
    
    echo "\nGetting sandbox for question...\n";
    $sandbox = $q->get_sandbox();
    echo "✓ Got sandbox: " . get_class($sandbox) . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>
