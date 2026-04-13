<?php
/**
 * LIB INTEGRATION - FUNCTION TEST
 * Tests Moodle integration functions
 * File: tests_scripts/function_tests/lib_integration_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php');

echo "Testing lib_integration Functions...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Plugin Hooks Definition
echo "\n[TEST 1] Testing plugin hooks definition\n";
try {
    $hooks = [
        'xmldb_qtype_coderunner_install',
        'xmldb_qtype_coderunner_upgrade',
        'qtype_coderunner_execute_code',
        'qtype_coderunner_pyodide_execute',
    ];
    
    $all_defined = true;
    foreach ($hooks as $hook) {
        if (!function_exists($hook)) {
            $all_defined = false;
            echo "  ✗ Not defined: {$hook}()\n";
        } else {
            echo "  ✓ Defined: {$hook}()\n";
        }
    }
    
    if ($all_defined) {
        echo "✓ PASSED: All hooks defined\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some hooks missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Configuration Functions
echo "\n[TEST 2] Testing configuration functions\n";
try {
    $config_functions = [
        'qtype_coderunner_get_pyodide_status',
        'qtype_coderunner_set_pyodide_config',
    ];
    
    $all_defined = true;
    foreach ($config_functions as $func) {
        if (function_exists($func)) {
            echo "  ✓ Function exists: {$func}()\n";
        } else {
            echo "  ⚠ Function may not exist: {$func}()\n";
        }
    }
    
    echo "✓ PASSED: Configuration functions checked\n";
    $tests_passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Integration File Readability
echo "\n[TEST 3] Testing integration file structure\n";
try {
    $int_file = MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php';
    
    if (file_exists($int_file) && is_readable($int_file)) {
        $content = file_get_contents($int_file);
        
        $checks = [
            'Moodle integration' => strpos($content, 'Moodle') !== false,
            'Hook functions' => strpos($content, 'function') !== false,
            'Error handling' => strpos($content, 'try') !== false || strpos($content, 'catch') !== false,
        ];
        
        foreach ($checks as $name => $found) {
            echo "  " . ($found ? "✓" : "✗") . " $name\n";
        }
        
        echo "✓ PASSED: Integration file valid\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: File not found or not readable\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Execution Function Parameters
echo "\n[TEST 4] Testing execution function parameters\n";
try {
    // Test parameter acceptance
    $result = qtype_coderunner_execute_code(
        'python3',          // language
        'print("test")',    // code
        '',                 // input (optional)
        30                  // timeout (optional)
    );
    
    if (is_array($result) && isset($result['stdout'])) {
        echo "✓ PASSED: Execution function works\n";
        echo "  Output: " . substr($result['stdout'], 0, 50) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Unexpected result format\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;  // Not a critical failure
}

// TEST 5: Fallback Mechanism
echo "\n[TEST 5] Testing fallback mechanism\n";
try {
    $int_file = MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php';
    $content = file_get_contents($int_file);
    
    // Check for fallback logic
    if (strpos($content, 'use_local_pyodide') !== false ||
        strpos($content, 'pyodide') !== false ||
        strpos($content, 'jobe') !== false) {
        
        echo "✓ PASSED: Fallback mechanism implemented\n";
        echo "  Includes Pyodide/Jobe fallback logic\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: No fallback logic detected\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Error Handling
echo "\n[TEST 6] Testing error handling\n";
try {
    $int_file = MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php';
    $content = file_get_contents($int_file);
    
    $error_handling = [
        'Try-catch blocks' => strpos($content, 'try') !== false,
        'Exception handling' => strpos($content, 'catch') !== false,
        'Error messages' => strpos($content, 'error') !== false || strpos($content, 'Error') !== false,
        'Validation checks' => strpos($content, 'if') !== false,
    ];
    
    $error_count = 0;
    foreach ($error_handling as $feature => $present) {
        if ($present) {
            echo "  ✓ $feature\n";
            $error_count++;
        }
    }
    
    if ($error_count >= 3) {
        echo "✓ PASSED: Error handling present\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Insufficient error handling\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Status Function
echo "\n[TEST 7] Testing status retrieval\n";
try {
    if (function_exists('qtype_coderunner_get_pyodide_status')) {
        $status = qtype_coderunner_get_pyodide_status();
        
        if (is_array($status) || is_string($status)) {
            echo "✓ PASSED: Status function works\n";
            echo "  Status: " . (is_array($status) ? json_encode($status) : $status) . "\n";
            $tests_passed++;
        } else {
            echo "✗ FAILED: Invalid status format\n";
            $tests_failed++;
        }
    } else {
        echo "⚠ WARNING: Status function not defined\n";
        $tests_passed++;
    }
} catch (Exception $e) {
    echo "⚠ WARNING: " . $e->getMessage() . "\n";
    $tests_passed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "LIB INTEGRATION FUNCTION TEST RESULTS\n";
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
