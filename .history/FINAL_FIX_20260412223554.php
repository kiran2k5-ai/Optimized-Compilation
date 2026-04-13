<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FINAL FIX - SETTING CORRECT GRADER AND TEMPLATE ===\n\n";

// Get Q20
$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

echo "Current state:\n";
echo "  Grader: {$q20->grader}\n";
echo "  Template length: " . strlen($q20->template) . "\n\n";

// Set grader to EqualityGrader (the simplest, most reliable)
$q20->grader = 'EqualityGrader';

// Set template to simple version that just outputs stdout
$q20->template = <<<'TEMPLATE'
import sys
from io import StringIO

# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ STUDENT_ANSWER }}
TEMPLATE;

$DB->update_record('question_coderunner_options', $q20);

echo "Updated to:\n";
echo "  Grader: EqualityGrader\n";
echo "  Template: Simple output (plain stdout)\n\n";

// Verify the change
$q20_verify = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
echo "✅ Verified in database:\n";
echo "  Grader: {$q20_verify->grader}\n";
echo "  Template snippet: " . substr($q20_verify->template, 0, 80) . "...\n\n";

// Clear all caches
purge_all_caches();
echo "✅ Caches cleared\n\n";

echo "Now follow these exact steps:\n";
echo "1. CLOSE BROWSER COMPLETELY (Alt+F4)\n";
echo "2. Navigate to: localhost/mod/quiz/view.php?id=2\n";
echo "3. Click 'Attempt quiz' (creates NEW attempt)\n";
echo "4. Click Check button\n";
echo "\nAll 3 tests should now show CORRECT ✅\n";
?>
