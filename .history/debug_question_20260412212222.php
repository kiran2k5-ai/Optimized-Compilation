<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DEBUGGING QUESTION 20 CONFIGURATION ===\n\n";

// Check the question
$q = $DB->get_record('question', ['id' => 20]);
echo "Question 20: {$q->name}\n";
echo "QType: {$q->qtype}\n\n";

// Check the options
$opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Options:\n";
echo "  coderunnertype: {$opts->coderunnertype}\n";
echo "  language: {$opts->language}\n";
echo "  sandbox: {$opts->sandbox}\n";
echo "  grader: {$opts->grader}\n";
echo "  template: " . (strlen($opts->template) > 100 ? substr($opts->template, 0, 100) . "..." : $opts->template) . "\n";
echo "  iscombinatortemplate: {$opts->iscombinatortemplate}\n";
echo "  answer: {$opts->answer}\n\n";

// Check tests
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20], 'id ASC');
echo "Tests (" . count($tests) . " total):\n";
foreach ($tests as $test) {
    echo "\nTest {$test->id}:\n";
    echo "  testcode: " . (strlen($test->testcode) > 50 ? substr($test->testcode, 0, 50) . "..." : $test->testcode) . "\n";
    echo "  input: " . var_export($test->input, true) . "\n";
    echo "  expected: " . var_export($test->expected, true) . "\n";
    echo "  display: {$test->display}\n";
    echo "  hiderestiffail: {$test->hiderestiffail}\n";
}

// Check if prototype exists
echo "\n\nChecking prototype...\n";
$prototype = $DB->get_record('question_coderunner_options', ['questionid' => 1]);
if ($prototype) {
    echo "Prototype exists (Question 1)\n";
    echo "  template length: " . strlen($prototype->template) . "\n";
} else {
    echo "No prototype found\n";
}
?>
