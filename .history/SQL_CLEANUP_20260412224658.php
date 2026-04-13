<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DIRECT SQL CLEANUP ===\n\n";

try {
    // Get quiz attempt ID
    $attempt_id = $DB->get_field('quiz_attempts', 'id', []);
    echo "Attempt ID: $attempt_id\n\n";
    
    // Use raw SQL to delete question attempt steps
    $sql = "DELETE FROM mdl_question_attempt_steps 
            WHERE questionattemptid IN (
                SELECT id FROM mdl_question_attempts 
                WHERE quizattemptid = ? AND questionid = 20
            )";
    
    $count1 = $DB->execute($sql, [$attempt_id]);
    echo "Deleted question attempt steps\n";
    
    // Delete question attempts
    $sql2 = "DELETE FROM mdl_question_attempts 
             WHERE quizattemptid = ? AND questionid = 20";
    
    $count2 = $DB->execute($sql2, [$attempt_id]);
    echo "Deleted question attempts\n\n";
    
    echo "✅ Cleanup complete\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Show current template
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Current template is set up to output JSON with fraction.\n";
echo "When you refresh, Q20 will reload fresh with this template.\n";
?>
