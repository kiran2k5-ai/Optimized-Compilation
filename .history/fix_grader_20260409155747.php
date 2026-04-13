<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FIXING GRADER FOR QUESTION 20 ===\n";

// Update question 20's grader
$update = new stdClass();
$update->id = 20;
$update->grader = 'TemplateGrader';

// Also need to update in the options table
$result = $DB->update_record('question_coderunner_options', [
    'questionid' => 20,
    'grader' => 'TemplateGrader'
]);

if ($result) {
    echo "✅ Successfully updated question 20\n";
    echo "  Grader changed from 'EBGrader.php' to 'TemplateGrader'\n";
    
    // Purge caches
    purge_all_caches();
    echo "  Caches purged\n";
} else {
    echo "❌ Failed to update!\n";
}

// Verify the change
$q_opts = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "\nVerification:\n";
echo "  Current grader: '" . $q_opts->grader . "'\n";
?>
