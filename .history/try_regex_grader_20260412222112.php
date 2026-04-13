<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== SWITCHING TO REGEX GRADER ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Current grader: {$q20->grader}\n";
echo "Switching to: RegexGrader\n\n";

// Change to RegexGrader
$q20->grader = 'RegexGrader';
$DB->update_record('question_coderunner_options', $q20);

echo "✅ Grader changed to RegexGrader\n";
echo "✅ RegexGrader compares output against expected value as regex\n\n";

// For RegexGrader, the expected value can be a simple string like "9"
// It will treat it as a regex pattern

// Also clear all caches
purge_all_caches();
echo "✅ Caches cleared\n\n";

// Verify
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "Verified grader: {$q20->grader}\n";
?>
