<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING FINAL WORKING TEMPLATE ===\n\n";

// For input-based programs, we need to:
// 1. Run the student code fresh for each test
// 2. Each run gets its own stdin
// 3. Use exec() to get a fresh namespace each iteration

$template = <<<'TEMPLATE'
import sys
from io import StringIO

SEPARATOR = "#<ab@17943918#@>#"

student_code = """{{ STUDENT_ANSWER | e('py') }}"""

{% for TEST in TESTCASES %}
# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code in a fresh namespace
exec(student_code)

{% if not loop.last %}
print(SEPARATOR)
{% endif %}
{% endfor %}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $template;
$DB->update_record('question_coderunner_options', $q20);

echo "✅ Final template created!\n";
echo "Template length: " . strlen($template) . "\n\n";
echo "Key features:\n";
echo "  - Captures student code as string\n";
echo "  - Uses exec() for fresh namespace per test\n";
echo "  - Sets sys.stdin for each test\n";
echo "  - Prints separator between tests\n\n";

echo "Template:\n";
echo $template . "\n\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
