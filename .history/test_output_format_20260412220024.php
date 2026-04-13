<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== SIMULATING TEST EXECUTION WITH EQUALITYGRADER ===\n\n";

$q20 = $DB->get_record('question_coderunner_options', ['questionid' => 20]);
$tests = $DB->get_records('question_coderunner_tests', ['questionid' => 20']);

// Get the template
$template_content = $q20->template;
$student_answer = $q20->answer;

echo "Template:\n";
echo $template_content . "\n\n";

echo "Student Answer:\n";
echo $student_answer . "\n\n";

// Simulate each test
foreach ($tests as $test) {
    echo "=== TEST {$test->id} ===\n";
    echo "Input (stdin): " . json_encode($test->stdin) . "\n";
    echo "Expected: " . json_encode($test->expected) . "\n";
    
    // What the template would produce:
    // It replaces {{ TEST.stdin }} and {{ STUDENT_ANSWER }}
    
    $test_input = $test->stdin;
    
    // After template substitution, the code becomes:
    $generated_code = <<<PYTHON
import sys
from io import StringIO

# Set up stdin for this test
test_input = """$test_input"""
sys.stdin = StringIO(test_input)

# Run the student code
$student_answer

# Test code (usually empty for input-based questions)

PYTHON;

    echo "\nGenerated code would be:\n";
    echo $generated_code . "\n";
    
    // Execute it
    $ch = curl_init('http://127.0.0.1/jobe/index.php/restapi/runs');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'run_spec' => [
            'language_id' => 'python3',
            'sourcecode' => $generated_code,
            'stdin' => ''
        ]
    ]));
    
    $resp = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($resp, true);
    
    echo "Mock API Response:\n";
    echo "  Outcome: {$result['outcome']} (15 = success)\n";
    echo "  Output: " . json_encode(trim($result['stdout'])) . "\n";
    echo "  Expected: " . json_encode($test->expected) . "\n";
    
    // EqualityGrader just compares output with expected
    $output_trimmed = trim($result['stdout']);
    $expected = trim($test->expected);
    
    if ($output_trimmed === $expected) {
        echo "✅ PASS - Output matches expected\n\n";
    } else {
        echo "❌ FAIL - Output doesn't match\n";
        echo "  Got: '$output_trimmed' (length: " . strlen($output_trimmed) . ")\n";
        echo "  Expected: '$expected' (length: " . strlen($expected) . ")\n\n";
    }
}
?>
