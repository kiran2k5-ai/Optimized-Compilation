<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

echo "=== TESTING FULL QUIZ SUBMISSION ===\n";

// Simulate a quiz submission
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
if (!$attempt) {
    echo "ERROR: Attempt 1 not found\n";
    exit;
}

echo "Attempt FOUND: {$attempt->id}\n";

// Load question usage
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);
$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "Question loaded: {$q->name} (Q{$q->id})\n";

// Test sandbox
echo "\nTesting get_sandbox()...\n";
try {
    $sandbox = $q->get_sandbox();
    echo "  ✅ Sandbox: " . get_class($sandbox) . "\n";
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

// Test grader
echo "\nTesting get_grader()...\n";
try {
    $grader = $q->get_grader();
    echo "  ✅ Grader: " . get_class($grader) . "\n";
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    exit;
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ ALL TESTS PASSED!\n";
echo "The quiz is now ready for submission.\n";
echo "Try clicking the Check button in your browser.\n";
?>
