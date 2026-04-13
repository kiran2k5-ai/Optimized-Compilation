<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== ANALYZING CODERUNNER TEST FLOW ===\n\n";

echo "For Python 'write a program' question:\n";
echo "1. Student answer: code that reads input()\n";
echo "2. Test cases: provide stdin for that code\n";
echo "3. Expected: expected stdout output\n\n";

echo "Template variables:\n";
echo "  STUDENT_ANSWER: the code student wrote\n";
echo "  TESTCASES: array of test objects\n"; 
echo "  TEST.testcode: code to run (should contain runner)\n";
echo "  TEST.stdin: input for the program\n";
echo "  TEST.expected: expected output\n\n";

// The issue is: what should testcode contain for an input-based program?
// Option 1: Run student_answer directly (but it needs stdin)
// Option 2: Import student_answer as module and call it
// Option 3: Use the template to handle it

// For python_w_input, it overrides input() to read from stdin
// For programs, the template should just run the student code once with the stdin

// Let me check if there's a python program prototype
$python_program = $DB->get_record_sql(
    "SELECT qco.* FROM {question_coderunner_options} qco 
     JOIN {question} q ON q.id = qco.questionid 
     WHERE qco.coderunnertype = 'python3_program' AND qco.prototypetype = 1"
);

if ($python_program) {
    echo "Found python3_program prototype!\n";
    echo "Template:\n" . substr($python_program->template, 0, 300) . "\n";
} else {
    echo "No python3_program prototype found\n";
}
?>
