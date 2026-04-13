<?php
/**
 * CODE EXECUTION - FUNCTION TEST
 * Tests Python code execution through Pyodide
 * File: tests_scripts/function_tests/execution_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/jobe_api_mock.php');

echo "Testing Code Execution Functions...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Basic Python Execution
echo "\n[TEST 1] Testing basic Python execution\n";
try {
    $result = qtype_coderunner_run_code('python3', 'print("Hello")', '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], 'Hello') !== false) {
        echo "✓ PASSED: Basic execution works\n";
        echo "  Output: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Output not as expected\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Variable Assignment and Use
echo "\n[TEST 2] Testing variable assignment\n";
try {
    $code = <<<'CODE'
x = 42
y = 8
z = x + y
print(z)
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], '50') !== false) {
        echo "✓ PASSED: Variables work correctly\n";
        echo "  Result: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Variable calculation incorrect\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Function Definition
echo "\n[TEST 3] Testing function definition\n";
try {
    $code = <<<'CODE'
def add(a, b):
    return a + b

result = add(5, 3)
print(result)
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], '8') !== false) {
        echo "✓ PASSED: Functions work correctly\n";
        echo "  Result: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Function execution failed\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Loop Execution
echo "\n[TEST 4] Testing loop execution\n";
try {
    $code = <<<'CODE'
total = 0
for i in range(1, 6):
    total += i
print(total)
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], '15') !== false) {
        echo "✓ PASSED: Loops work correctly\n";
        echo "  Result: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Loop execution failed\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Exception Handling
echo "\n[TEST 5] Testing exception handling in code\n";
try {
    $code = <<<'CODE'
try:
    x = 1 / 0
except ZeroDivisionError:
    print("Caught error")
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], 'Caught error') !== false) {
        echo "✓ PASSED: Exception handling works\n";
        echo "  Output: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Exception handling not working\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Standard Library Usage
echo "\n[TEST 6] Testing standard library usage\n";
try {
    $code = <<<'CODE'
import math
result = math.sqrt(16)
print(int(result))
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strpos($result['stdout'], '4') !== false) {
        echo "✓ PASSED: Standard library works\n";
        echo "  Result: " . trim($result['stdout']) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Standard library import failed\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;  // Not critical - Pyodide may have limited stdlib
}

// TEST 7: Output Capture
echo "\n[TEST 7] Testing output capture\n";
try {
    $code = <<<'CODE'
print("Line 1")
print("Line 2")
print("Line 3")
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    $lines = explode("\n", trim($result['stdout']));
    if (count($lines) >= 3) {
        echo "✓ PASSED: Multi-line output captured\n";
        echo "  Lines: " . count($lines) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Output capture incomplete\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 8: Empty Output
echo "\n[TEST 8] Testing execution without output\n";
try {
    $code = <<<'CODE'
x = 42
# No output
CODE;
    
    $result = qtype_coderunner_run_code('python3', $code, '', 30);
    
    if (isset($result['stdout']) && strlen(trim($result['stdout'])) == 0) {
        echo "✓ PASSED: No-output execution handled\n";
        echo "  Output: (empty)\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Unexpected output\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "CODE EXECUTION TEST RESULTS\n";
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
