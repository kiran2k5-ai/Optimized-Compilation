<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING TEMPLATE-GRADER COMPATIBLE TEMPLATE ===\n\n";

// TemplateGrader expects template to output JSON with "fraction" field
// Let's create a template that outputs the correct JSON format

$template = <<<'TEMPLATE'
import sys
import json
from io import StringIO

# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ STUDENT_ANSWER }}

# For TemplateGrader, we need to output JSON with a fraction field
# Just capture stdout and compare with expected
// This template runs the code and outputs its stdout
// The TemplateGrader will compare this with expected output
TEMPLATE;

// Actually, let me use a simpler approach - just output the actual output
// The TemplateGrader should handle it

$simple_template = <<<'TEMPLATE'
import sys
from io import StringIO

# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ STUDENT_ANSWER }}

# Test code (empty for input-based programs)
{{ TEST.testcode }}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$q20->template = $simple_template;

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Updated template\n";
echo "Length: " . strlen($simple_template) . "\n\n";

// ALSO make sure to set the grader explicitly to TemplateGrader
// $q20->grader = 'TemplateGrader';
// $DB->update_record('question_coderunner_options', $q20);
// echo "✅ Set grader to TemplateGrader\n\n";

// Actually, check if the issue is that the attempted output format doesn't match
// Let me also check if we need to output JSON

echo "The template should work with EqualityGrader now.\n";
echo "Just make sure to:\n";
echo "1. Close browser completely\n";
echo "2. Clear all browser cache\n";
echo "3. Open new browser window\n";
echo "4. Navigate to: http://localhost/mod/quiz/attempt.php?attempt=1\n";
?>
