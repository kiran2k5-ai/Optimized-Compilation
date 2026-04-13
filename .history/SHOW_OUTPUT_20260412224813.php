<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== SWITCHING TO EQUALITYGRADER WITH SIMPLE OUTPUT ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

// Simple template that outputs plain text only
$simple_output_template = <<<'TEMPLATE'
import sys
from io import StringIO

# Set stdin
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ STUDENT_ANSWER }}
TEMPLATE;

$q20->iscombinatortemplate = 0;
$q20->template = $simple_output_template;
$q20->grader = 'EqualityGrader';  // Simple grader that shows output

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Updated Q20:\n";
echo "   Grader: EqualityGrader (shows actual output in 'Got' column)\n";
echo "   Template: Simple - outputs plain numbers (9, 40, 78)\n\n";

purge_all_caches();
echo "✅ Cache cleared\n\n";

echo "Just refresh (F5) and click Check.\n";
echo "Now you should see the actual output numbers in the 'Got' column.\n";
?>
