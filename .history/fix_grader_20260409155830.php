<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FIXING GRADER FOR QUESTION 20 ===\n";

// Update in the options table (where the grader is actually stored)
$record = new stdClass();
$record->id = $DB->get_field('question_coderunner_options', 'id', ['questionid' => 20]);
$record->grader = 'TemplateGrader';

$result = $DB->update_record('question_coderunner_options', $record);

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
