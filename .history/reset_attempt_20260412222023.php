<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== RESETTING QUIZ ATTEMPT ===\n\n";

// Delete the old quiz attempt
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
if ($attempt) {
    echo "Found attempt 1:\n";
    echo "  Quiz ID: {$attempt->quiz}\n";
    echo "  User ID: {$attempt->userid}\n";
    echo "  State: {$attempt->state}\n\n";
    
    echo "Deleting attempt 1 and all its data...\n";
    
    // Delete step grades
    $DB->delete_records('question_attempt_steps', ['questionattemptid' => $attempt->id]);
    
    // Delete question attempts
    $DB->delete_records('question_attempts', ['quizattemptid' => $attempt->id]);
    
    // Delete the quiz attempt itself
    $DB->delete_records('quiz_attempts', ['id' => 1]);
    
    echo "✅ Attempt deleted\n\n";
} else {
    echo "No attempt found\n\n";
}

// Verify Q20 grader is correct
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Q20 Grader: {$q20->grader}\n";
echo "Q20 Template length: " . strlen($q20->template) . "\n\n";

echo "⚠️  NOW DO THIS:\n";
echo "1. Close browser completely\n";
echo "2. Go to: http://localhost/mod/quiz/view.php?id=2\n";
echo "3. Click 'Attempt quiz'\n";
echo "4. This creates a NEW attempt with fresh question data\n";
echo "5. Click Check button\n";
?>
