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

echo "\nLoading question usage...\n";
try {
    $quba = question_engine::load_questions_usage_by_activity($attempt->uniqueid);
    echo "Question usage loaded\n";
    
    // Get each question
    $slots = $quba->get_slots();
    echo "Slots: " . implode(', ', $slots) . "\n";
    
    foreach ($slots as $slot) {
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
            
            // Try to get sandbox
            echo "    Trying to get sandbox instance...\n";
            try {
                if (method_exists($q, 'get_sandbox')) {
                    $sb = $q->get_sandbox();
                    echo "    Sandbox instance: " . get_class($sb) . "\n";
                } else {
                    echo "    ERROR: Question doesn't have get_sandbox method\n";
                }
            } catch (Exception $e2) {
                echo "    ERROR in get_sandbox: " . $e2->getMessage() . "\n";
                echo "    Line: " . $e2->getLine() . " in " . $e2->getFile() . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    $trace = $e->getTrace();
    foreach ($trace as $i => $t) {
        echo "  #{$i} " . ($t['class'] ?? '') . ($t['class'] ? '::' : '') . $t['function'] . "() at " . $t['file'] . ":" . $t['line'] . "\n";
    }
}
?>
