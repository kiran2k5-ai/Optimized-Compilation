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
    
    // The template with substitutions
    $test_input = addcslashes($test->stdin, '"\\');
    $code = addcslashes($student_answer, '"\\');
    
    // Build the code that would be generated
    $generated_code = "import sys\nfrom io import StringIO\n\n";
    $generated_code .= "# Set up stdin for this test\n";
    $generated_code .= "test_input = \"\"\"" . str_replace('\\n', "\n", $test->stdin) . "\"\"\"\n";
    $generated_code .= "sys.stdin = StringIO(test_input)\n\n";
    $generated_code .= "# Run the student code\n";
    $generated_code .= $student_answer . "\n\n";
    $generated_code .= "# Test code (usually empty for input-based questions)\n";
    
    echo "\nGenerated code:\n";
    echo "─────────────────────────────────\n";
    echo $generated_code . "\n";
    echo "─────────────────────────────────\n\n";
    
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
    
    echo "API Response:\n";
    echo "  Outcome: {$result['outcome']} (15 = success)\n";
    echo "  Raw output: " . json_encode($result['stdout']) . "\n";
    
    // EqualityGrader comparison (trims whitespace)
    $output_trimmed = trim($result['stdout']);
    $expected_trimmed = trim($test->expected);
    
    echo "  After trim: " . json_encode($output_trimmed) . "\n";
    echo "  Expected: " . json_encode($expected_trimmed) . "\n";
    
    if ($output_trimmed === $expected_trimmed) {
        echo "✅ PASS - Output matches expected\n\n";
    } else {
        echo "❌ FAIL - Output doesn't match\n";
        echo "  Output char codes: " . implode(',', array_map('ord', str_split($output_trimmed))) . "\n";
        echo "  Expected char codes: " . implode(',', array_map('ord', str_split($expected_trimmed))) . "\n\n";
    }
}
?>
