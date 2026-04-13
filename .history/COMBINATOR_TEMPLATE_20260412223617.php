<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING TEMPLATE GRADER COMPATIBLE TEMPLATE ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

// For TemplateGrader, we need to output JSON with fraction field
// The template should output something that when compared with expected, gives a fraction

// Actually, let me use a combinator template since that works better with TemplateGrader
$combinator_template = <<<'TEMPLATE'
import sys
from io import StringIO

SEPARATOR = "#<ab@17943918#@>#"

{% for TEST in TESTCASES %}
# Set up stdin
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run student code
{{ STUDENT_ANSWER }}

{% if not loop.last %}
print(SEPARATOR)
{% endif %}
{% endfor %}
TEMPLATE;

// Enable combinator mode
$q20->iscombinatortemplate = 1;
$q20->template = $combinator_template;
$q20->grader = 'TemplateGrader';  // Explicitly set for combinator
$q20->testsplitterre = '#<ab@17943918#@>#';  // Separator for splitting output

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Updated Q20 with combinator template for TemplateGrader\n";
echo "   Grader: TemplateGrader\n";
echo "   Is combinator: YES\n";
echo "   Template length: " . strlen($combinator_template) . "\n\n";

purge_all_caches();
echo "✅ Cache cleared\n\n";

echo "This template outputs all 3 test results separated by #<ab@17943918#@>#\n";
echo "TemplateGrader will split by separator and compare each output\n";
?>
