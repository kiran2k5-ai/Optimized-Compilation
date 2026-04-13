<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHANGING GRADER TO EQUALITYGRADER ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Before:\n";
echo "  Grader: {$q20->grader}\n\n";

// Change from TemplateGrader to EqualityGrader
$q20->grader = 'EqualityGrader';

$DB->update_record('question_coderunner_options', $q20);

echo "After:\n";
echo "  Grader: {$q20->grader}\n\n";

echo "Why EqualityGrader?\n";
echo "- Takes template output (e.g., '9')\n";
echo "- Compares with expected value\n";
echo "- No JSON formatting needed\n";
echo "- Perfect for output-based testing\n\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
