<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== SIMULATING QUIZ ATTEMPT SUBMISSION ===\n";

// Load the quiz
$quiz = $DB->get_record('quiz', ['id' => 1]);
if (!$quiz) {
    echo "Quiz 1 not found!\n";
    exit;
}
echo "Quiz: {$quiz->name}\n";

// Get the attempt
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
if (!$attempt) {
    echo "Attempt 1 not found!\n";
    exit;
}
echo "Attempt: ID {$attempt->id}, State: {$attempt->state}\n";

// Load the question usage
require_once($CFG->dirroot . '/question/engine/lib.php');

echo "\nLoading question usage for attempt {$attempt->uniqueid}...\n";
try {
    $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);
    echo "Question usage loaded: " . $quba->get_question_count() . " questions\n";
    
    // Get each question
    foreach ($quba->get_slots() as $slot) {
        $qa = $quba->get_question_attempt($slot);
        $q = $qa->get_question();
        echo "\n  Slot $slot: Q{$q->id} ({$q->qtype})\n";
        echo "    Name: {$q->name}\n";
        
        if ($q->qtype === 'coderunner') {
            echo "    Type: CodeRunner\n";
            if (isset($q->options)) {
                echo "    Sandbox: " . ($q->options->sandbox ?? 'NOT SET') . "\n";
                echo "    Language: " . ($q->options->language ?? 'NOT SET') . "\n";
            } else {
                echo "    ERROR: No options object!\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR loading question usage:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>
