<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FORCING FRESH QUESTION LOAD ===\n\n";

// Get the current quiz attempt
$attempt = $DB->get_record('quiz_attempts', ['id' => 1]);
echo "Quiz attempt 1 found\n";
echo "  Quiz ID: {$attempt->quiz}\n\n";

// Find question attempts for Q20 in this attempt
$q_attempts = $DB->get_records('question_attempts', [
    'quizattemptid' => $attempt->id,
    'questionid' => 20
]);

echo "Found " . count($q_attempts) . " question attempt(s) for Q20\n";

foreach ($q_attempts as $qa) {
    echo "\nDeleting question attempt {$qa->id}...\n";
    
    // Delete the steps first (they reference the question attempt)
    $deleted_steps = $DB->delete_records('question_attempt_steps', ['questionattemptid' => $qa->id]);
    echo "  Deleted $deleted_steps steps\n";
    
    // Delete the question attempt
    $DB->delete_records('question_attempts', ['id' => $qa->id]);
    echo "  Deleted question attempt\n";
}

echo "\n✅ Done! The question attempt has been cleared.\n";
echo "   When you reload the page, Q20 will be re-created fresh\n";
echo "   using the current template from the database.\n\n";

// Verify current template
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Current Q20 Template (first 100 chars):\n";
echo substr($q20->template, 0, 100) . "...\n\n";

echo "Now just refresh the page (F5) and click Check!\n";
?>
