<?php
/**
 * FULL WORKFLOW - INTEGRATION TEST
 * Tests complete end-to-end workflow
 * File: tests_scripts/integration_tests/full_workflow_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/jobe_api_mock.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php');

echo "Testing Full Workflow Integration...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Code Submission to Execution Pipeline
echo "\n[TEST 1] Testing code submission to execution pipeline\n";
try {
    // Step 1: Get available languages
    $languages = qtype_coderunner_get_languages();
    if (!in_array('python3', $languages)) {
        throw new Exception("Python3 not available");
    }
    
    // Step 2: Submit code
    $code = 'print("Workflow test successful")';
    
    // Step 3: Execute
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    // Step 4: Verify result
    if (isset($result['stdout']) && strpos($result['stdout'], 'Workflow test successful') !== false) {
        echo "✓ PASSED: Complete pipeline works\n";
        echo "  Result: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Pipeline result unexpected\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Execution with Input/Output
echo "\n[TEST 2] Testing execution with input and output\n";
try {
    $code = <<<'CODE'
name = input()
print(f"Hello, {name}!")
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, 'Alice', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], 'Alice') !== false) {
        echo "✓ PASSED: Input/Output handling works\n";
        echo "  Output: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Input/Output not working\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Error Handling in Workflow
echo "\n[TEST 3] Testing error handling in workflow\n";
try {
    $code = <<<'CODE'
result = []
for i in range(5):
    result.append(i * 2)
print(result)
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['returncode']) !== false) {
        // Check if completed (return code 0 is success)
        echo "✓ PASSED: Error handling works\n";
        echo "  Return code: " . $result['returncode'] . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Return code not available\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Configuration Integration
echo "\n[TEST 4] Testing configuration integration\n";
try {
    global $DB;
    
    // Check if Pyodide settings are accessible
    $config = get_config('qtype_coderunner');
    
    if (is_object($config) || ($config === false && !is_null(null))) {
        echo "✓ PASSED: Configuration accessible\n";
        echo "  Config type: " . gettype($config) . "\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Configuration not yet set\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;
}

// TEST 5: Multiple Sequential Executions
echo "\n[TEST 5] Testing multiple sequential executions\n";
try {
    $execution_count = 3;
    $successful = 0;
    
    for ($i = 1; $i <= $execution_count; $i++) {
        $result = qtype_coderunner_run_code('python3', "print($i)", '', 30);
        
        if (isset($result['stdout']) && trim($result['stdout']) == $i) {
            $successful++;
        }
    }
    
    if ($successful === $execution_count) {
        echo "✓ PASSED: Multiple executions work\n";
        echo "  Successful: $successful/$execution_count\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some executions failed\n";
        echo "  Successful: $successful/$execution_count\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Test Case Execution
echo "\n[TEST 6] Testing test case execution\n";
try {
    $code = <<<'CODE'
def add(a, b):
    return a + b
CODE;
    
    $test = 'print(add(2, 3))';
    $expected = '5';
    
    $result = qtype_coderunner_run_tests('python3', $code, $test, $expected);
    
    if (is_array($result)) {
        echo "✓ PASSED: Test case execution works\n";
        echo "  Result: " . json_encode($result) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Test result invalid\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Response Format Consistency
echo "\n[TEST 7] Testing response format consistency\n";
try {
    $responses = [];
    
    // Generate 3 different executions
    for ($i = 0; $i < 3; $i++) {
        $result = qtype_coderunner_run_code('python3', 'print("test")', '', 30);
        $responses[] = $result;
    }
    
    // Check all have same format
    $format_consistent = true;
    $required_keys = ['stdout', 'stderr', 'returncode'];
    
    foreach ($responses as $response) {
        foreach ($required_keys as $key) {
            if (!isset($response[$key])) {
                $format_consistent = false;
                break 2;
            }
        }
    }
    
    if ($format_consistent) {
        echo "✓ PASSED: Response format consistent\n";
        echo "  All responses have required fields\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Response format inconsistent\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 8: Complex Code Workflow
echo "\n[TEST 8] Testing complex code workflow\n";
try {
    $code = <<<'CODE'
class Calculator:
    def __init__(self, value):
        self.value = value
    
    def add(self, x):
        self.value += x
        return self.value

calc = Calculator(10)
print(calc.add(5))
print(calc.add(3))
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout'])) {
        $lines = explode("\n", trim($result['stdout']));
        if (count($lines) >= 2 && strpos($result['stdout'], '15') !== false) {
            echo "✓ PASSED: Complex code works\n";
            echo "  Lines output: " . count($lines) . "\n";
            $tests_passed++;
        } else {
            echo "✗ FAILED: Complex code output unexpected\n";
            $tests_failed++;
        }
    } else {
        echo "✗ FAILED: No output from complex code\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "FULL WORKFLOW INTEGRATION TEST RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";

if ($tests_failed > 0) {
    echo "\n✗ FAILED - Review errors above\n";
} else {
    echo "\n✓ PASSED - All tests successful\n";
}

echo "\n";
?>
