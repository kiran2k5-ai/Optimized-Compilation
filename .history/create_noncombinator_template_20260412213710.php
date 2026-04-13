<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CREATING NON-COMBINATOR TEMPLATE ===\n\n";

// For non-combinator: template runs once per test
// CodeRunner calls it repeatedly with different test data
// So we don't need separators or a loop

$template = <<<'TEMPLATE'
import sys
from io import StringIO

# Set up stdin for this test
test_input = """{{ TEST.stdin | e('py') }}"""
sys.stdin = StringIO(test_input)

# Run the student code
{{ STUDENT_ANSWER }}

# Test code (usually empty for input-based questions)
{{ TEST.testcode }}
TEMPLATE;

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);

// Make sure it's NOT marked as combinator
$q20->iscombinatortemplate = 0;
$q20->template = $template;

$DB->update_record('question_coderunner_options', $q20);

echo "✅ Non-combinator template created!\n";
echo "Template length: " . strlen($template) . "\n\n";
echo "Template:\n";
echo $template . "\n\n";

echo "Settings:\n";
echo "  iscombinatortemplate: {$q20->iscombinatortemplate}\n";
echo "  enablecombinator: {$q20->enablecombinator}\n\n";

purge_all_caches();
echo "✅ Cache cleared!\n";
?>
