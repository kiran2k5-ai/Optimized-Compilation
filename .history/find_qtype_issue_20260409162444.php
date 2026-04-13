<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

// The problem: question->qtype is an OBJECT instead of a string
// Let's fix it by replacing the qtype object with the qtype string

$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "BEFORE FIX:\n";
echo "  Q->qtype = " . (is_object($q->qtype) ? "Object(" . get_class($q->qtype) . ")" : $q->qtype) . "\n";
echo "  Q->qtype class: " . get_class($q->qtype) . "\n";

// Get the qtype name from the question base properties
$qtype_name = $q->qtype_name ?? $DB->get_field('question', 'qtype', ['id' => $q->id]);

echo "\nCORRECTION:  Q->qtype should be string 'coderunner', not object\n";

// The fix: Replace the qtype object with a string
$q->qtype = 'coderunner';

echo "\nAFTER FIX:\n";
echo "  Q->qtype = '" . $q->qtype . "' (string)\n";

// Now try to call get_sandbox()
echo "\nTrying get_sandbox() now...\n";
try {
    $sandbox = $q->get_sandbox();
    echo "SUCCESS! Sandbox: " . get_class($sandbox) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
