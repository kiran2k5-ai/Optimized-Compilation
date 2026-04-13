<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FIXING TEMPLATE - STUDENT CODE IN TEST LOOP ===\n\n";

$template = <<<'TEMPLATE'
import sys
from io import StringIO

SEPARATOR = "#<ab@17943918#@>#"

{% for TEST in TESTCASES %}
# Redirect stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code with this input
{{ STUDENT_ANSWER }}

{{ TEST.testcode }}

{% if not loop.last %}
print(SEPARATOR)
{% endif %}
{% endfor %}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $template;
$DB->update_record('question_coderunner_options', $q20);

echo "✅ Fixed template created!\n";
echo "Template length: " . strlen($template) . "\n\n";
echo "Key change: STUDENT_ANSWER moved INSIDE test loop\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
