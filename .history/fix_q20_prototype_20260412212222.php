<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING QUESTION 20 ===\n\n";

$q = $DB->get_record('question', ['id' => 20]);
$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Before Fix:\n";
echo "  Coderunner Type: {$opts->coderunnertype}\n";
echo "  Language: {$opts->language}\n";
echo "  Prototype: {$opts->prototype}\n";
echo "  Template Length: " . strlen($opts->template) . "\n\n";

// Q17 is python3_w_input which is better for questions that need input
$python_proto = $DB->get_record('question_coderunner_options', ['questionid' => 17]);

echo "Setting Q20 to use Python3 with Input Prototype (Q17):\n";
echo "  Type: {$python_proto->coderunnertype}\n";
echo "  Language: {$python_proto->language}\n\n";

// Update Q20 to inherit from Q17
$opts->prototype = 17;
$opts->coderunnertype = $python_proto->coderunnertype;
$opts->template = '';  // Empty so it inherits
$opts->language = 'python3';

$DB->update_record('question_coderunner_options', $opts);

echo "✅ Question 20 updated!\n\n";

// Verify
$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "After Fix:\n";
echo "  Coderunner Type: {$opts->coderunnertype}\n";
echo "  Language: {$opts->language}\n";
echo "  Prototype: {$opts->prototype}\n";
echo "  Template Length: " . strlen($opts->template) . "\n";
?>
