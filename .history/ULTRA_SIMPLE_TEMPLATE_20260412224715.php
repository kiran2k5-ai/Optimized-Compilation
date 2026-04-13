<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING SIMPLE, BULLETPROOF TEMPLATE ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

// Much simpler template - no stdout redirection, just direct output
$simple_template = <<<'TEMPLATE'
import sys
from io import StringIO
import json

# Set stdin
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run code and capture output
import subprocess
import tempfile
import os

# Write student code to temp file
code = '''{{ STUDENT_ANSWER }}'''

with tempfile.NamedTemporaryFile(mode='w', suffix='.py', delete=False) as f:
    f.write(code)
    temp_file = f.name

try:
    # Run with stdin
    stdin_data = test_input.encode()
    result = subprocess.run(['python', temp_file], input=stdin_data, capture_output=True, text=True, timeout=5)
    actual_output = result.stdout.strip()
finally:
    os.unlink(temp_file)

# Compare
expected = """{{ TEST.expected | e('py') }}""".strip()
matches = actual_output == expected

# Return JSON for TemplateGrader
print(json.dumps({"fraction": 1.0 if matches else 0.0}))
TEMPLATE;

// Actually wait, that's too complex and spawns another python process. Let me use the simplest possible approach

$ultra_simple_template = <<<'TEMPLATE'
import sys
from io import StringIO
import json

# Redirect stdin
test_stdin = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_stdin)

# Capture original stdout
import io
captured = io.StringIO()
orig_stdout = sys.stdout
sys.stdout = captured

try:
    {{ STUDENT_ANSWER }}
finally:
    sys.stdout = orig_stdout

# Get output and compare
actual = captured.getvalue().strip()
expected = """{{ TEST.expected | e('py') }}""".strip()

# Return fraction for TemplateGrader
import json
print(json.dumps({"fraction": 1.0 if actual == expected else 0.0}))
TEMPLATE;

$q20->iscombinatortemplate = 0;
$q20->template = $ultra_simple_template;
$q20->grader = 'TemplateGrader';

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Updated with ultra-simple template\n";
echo "   Template uses basic StringIO for stdin redirection\n";
echo "   Outputs JSON: {\"fraction\": 1.0}\n\n";

purge_all_caches();
echo "✅ Caches purged\n\n";

echo "Now reload the page and click Check!\n";
?>
