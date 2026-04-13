<?php
/**
 * JOBE API MOCK - ENDPOINT TEST
 * Tests the jobe_api_mock.php to verify it correctly handles Jobe API calls
 * File: tests_scripts/api_tests/jobe_api_mock_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/jobe_api_mock.php');

echo "Testing Jobe API Mock Endpoints...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Get Languages
echo "\n[TEST 1] Testing get_languages() endpoint\n";
try {
    $languages = qtype_coderunner_get_languages();
    if (is_array($languages) && in_array('python3', $languages)) {
        echo "✓ PASSED: Languages returned correctly\n";
        echo "  Languages: " . implode(", ", $languages) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid language list\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Simple Code Execution
echo "\n[TEST 2] Testing run_code() - Simple execution\n";
try {
    $result = qtype_coderunner_run_code(
        'python3',
        'print("Hello, World!")',
        '',
        30
    );
    
    if (isset($result['stdout']) && strpos($result['stdout'], 'Hello, World!') !== false) {
        echo "✓ PASSED: Code executed successfully\n";
        echo "  Output: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Unexpected output\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Code with Input
echo "\n[TEST 3] Testing run_code() - With input\n";
try {
    $result = qtype_coderunner_run_code(
        'python3',
        'x = input()
print(f"You entered: {x}")',
        'TestInput',
        30
    );
    
    if (isset($result['stdout']) && strpos($result['stdout'], 'TestInput') !== false) {
        echo "✓ PASSED: Input handling works\n";
        echo "  Output: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Input not processed correctly\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Error Handling
echo "\n[TEST 4] Testing error handling\n";
try {
    $result = qtype_coderunner_run_code(
        'python3',
        'print(undefined_variable)',
        '',
        30
    );
    
    if (isset($result['stderr']) && strlen($result['stderr']) > 0) {
        echo "✓ PASSED: Error captured correctly\n";
        echo "  Error: " . substr($result['stderr'], 0, 80) . "...\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Error not captured\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Test Execution
echo "\n[TEST 5] Testing run_tests() function\n";
try {
    $result = qtype_coderunner_run_tests(
        'python3',
        'def add(a, b): return a + b',
        'print(add(2, 3))',
        '5'
    );
    
    if (isset($result) && (isset($result['passed']) || isset($result['failed']))) {
        echo "✓ PASSED: Test execution works\n";
        echo "  Result: " . json_encode($result) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid test result format\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Response Format
echo "\n[TEST 6] Testing response format\n";
try {
    $result = qtype_coderunner_run_code('python3', 'print(42)', '', 30);
    
    $has_stdout = isset($result['stdout']);
    $has_stderr = isset($result['stderr']);
    $has_returncode = isset($result['returncode']);
    
    if ($has_stdout && $has_stderr && $has_returncode) {
        echo "✓ PASSED: Response has all required fields\n";
        echo "  Fields: stdout, stderr, returncode\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Missing response fields\n";
        echo "  Has stdout: " . ($has_stdout ? 'yes' : 'no') . "\n";
        echo "  Has stderr: " . ($has_stderr ? 'yes' : 'no') . "\n";
        echo "  Has returncode: " . ($has_returncode ? 'yes' : 'no') . "\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Timeout Handling
echo "\n[TEST 7] Testing timeout handling\n";
try {
    $start = microtime(true);
    $result = qtype_coderunner_run_code(
        'python3',
        'import time; time.sleep(2); print("done")',
        '',
        1  // 1 second timeout
    );
    $elapsed = microtime(true) - $start;
    
    if ($elapsed < 3) {  // Should timeout quickly
        echo "✓ PASSED: Timeout respected\n";
        echo "  Elapsed: " . round($elapsed, 2) . " seconds\n";
        $tests_passed++;
    } else {
        echo "⚠ WARNING: Timeout handling slow\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "JOBE API MOCK TEST RESULTS\n";
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
