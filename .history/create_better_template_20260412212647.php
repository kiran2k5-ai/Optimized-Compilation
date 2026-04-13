<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING PROPER INPUT-BASED TEMPLATE ===\n\n";

// For input-based programs, we need a template that:
// 1. Runs the student code for each test
// 2. Provides stdin from TEST.stdin
// 3. Captures stdout output
// 4. Prints separator between tests

$template = <<<'TEMPLATE'
import sys
from io import StringIO

{{ STUDENT_ANSWER }}

SEPARATOR = "#<ab@17943918#@>#"

{% for TEST in TESTCASES %}
# Redirect stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ TEST.testcode }}

{% if not loop.last %}
print(SEPARATOR)
{% endif %}
{% endfor %}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $template;
$DB->update_record('question_coderunner_options', $q20);

echo "✅ New template set!\n";
echo "Length: " . strlen($template) . "\n\n";

echo "Template:\n";
echo $template . "\n\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
