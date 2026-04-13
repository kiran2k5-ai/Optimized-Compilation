<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/engine/lib.php';
global $DB;

// Simulate attempting to process a quiz submission
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
$quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);

$qa = $quba->get_question_attempt(1);
$q = $qa->get_question();

echo "Question loaded successfully\n";
echo "Question class: " . get_class($q) . "\n";
echo "\n";

// Try to grade the response like processattempt does
echo "Attempting to grade response...\n";
try {
    // Simulate form submission response
    $response = ['answer' => 'print(9)'];
    
    // This is where the error might happen
    $qa->process_response($response);
    echo "Response processed\n";
    
    // Try to finalize
    $quba->finish_question_attempt($qa, 1);
    echo "Attempt finished\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    foreach ($e->getTrace() as $i => $t) {
        $func = ($t['class'] ?? '?') . '::' . $t['function'];
        $file = basename($t['file'] ?? '?');
        echo "  #{$i} $func() at $file:" . ($t['line'] ?? '?') . "\n";
    }
}
?>
