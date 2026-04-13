<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== DELETING QUIZ ATTEMPT WITH FORCE ===\n\n";

// Try to disable foreign keys and delete
echo "Attempting to delete attempt 1...\n";
try {
    // Disable foreign key checks
    $DB->execute("SET FOREIGN_KEY_CHECKS = 0");
    
    // Get all question attempts
    $q_attempts = $DB->get_field_sql("SELECT GROUP_CONCAT(id) FROM {question_attempts} WHERE quizattemptid = 1");
    
    if ($q_attempts) {
        // Delete steps for these attempts
        $DB->delete_records_select('question_attempt_steps', "questionattemptid IN ($q_attempts)");
        echo "✅ Deleted question attempt steps\n";
    }
    
    // Delete question attempts
    $DB->delete_records('question_attempts', ['quizattemptid' => 1]);
    echo "✅ Deleted question attempts\n";
    
    // Delete quiz attempt
    $DB->delete_records('quiz_attempts', ['id' => 1]);
    echo "✅ Deleted quiz attempt\n";
    
    // Re-enable foreign keys
    $DB->execute("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Re-enabled foreign key checks\n\n";
    
    // Clear caches
    purge_all_caches();
    echo "✅ Caches cleared\n\n";
    
    echo "SUCCESS! Now:\n";
    echo "1. Close browser\n";
    echo "2. Navigate to: http://localhost/mod/quiz/view.php?id=2\n";
    echo "3. Click 'Attempt quiz' (creates fresh attempt)\n";
    echo "4. Click Check\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    try {
        $DB->execute("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e2) {}
}
?>
