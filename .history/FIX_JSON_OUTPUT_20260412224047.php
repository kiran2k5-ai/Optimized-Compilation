<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== FIXING TEMPLATE TO OUTPUT JSON WITH FRACTION ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

// TemplateGrader expects JSON with "fraction" field
// For a non-combinator template (runs per test), output should be JSON
// For a combinator template, jobrunner handles the parsing

// Let's use NON-combinator mode with JSON output
$template_json_output = <<<'TEMPLATE'
import sys
from io import StringIO
import json

# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Capture stdout
from io import StringIO as SIO
old_stdout = sys.stdout
sys.stdout = SIO()

# Run the student code
{{ STUDENT_ANSWER }}

# Get the output
output = sys.stdout.getvalue()
sys.stdout = old_stdout

# Compare with expected
expected = """{{ TEST.expected | e('py') }}"""
output_trimmed = output.strip()
expected_trimmed = expected.strip()

# TemplateGrader expects JSON with fraction field
result = {
    "fraction": 1.0 if output_trimmed == expected_trimmed else 0.0,
    "passed": output_trimmed == expected_trimmed
}

print(json.dumps(result))
TEMPLATE;

// Switch to non-combinator mode
$q20->iscombinatortemplate = 0;
$q20->template = $template_json_output;
$q20->grader = 'TemplateGrader';

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Updated Q20:\n";
echo "   Is combinator: NO (per-test mode)\n";
echo "   Grader: TemplateGrader\n";
echo "   Template: Outputs JSON with fraction field\n\n";

purge_all_caches();
echo "✅ Cache cleared\n\n";

echo "Now the template outputs JSON like: {\"fraction\": 1.0}\n";
echo "TemplateGrader will parse this and assign marks\n\n";

echo "Just refresh the page (F5) and click Check!\n";
?>
