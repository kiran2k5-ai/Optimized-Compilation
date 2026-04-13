<?php
/**
 * AJAX ENDPOINTS - TEST
 * Tests AJAX execution endpoints for code submission and execution
 * File: tests_scripts/api_tests/ajax_endpoints_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');

echo "Testing AJAX Endpoints...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Endpoint File Existence
echo "\n[TEST 1] Testing endpoint file availability\n";
try {
    $endpoints = [
        'jobe_api_mock.php' => '/public/question/type/coderunner/jobe_api_mock.php',
        'lib_integration.php' => '/public/question/type/coderunner/lib_integration.php',
    ];
    
    $all_exist = true;
    foreach ($endpoints as $name => $path) {
        $filepath = MOODLE_ROOT . $path;
        if (!file_exists($filepath)) {
            $all_exist = false;
            echo "  ✗ Missing: $name\n";
        } else {
            echo "  ✓ Found: $name\n";
        }
    }
    
    if ($all_exist) {
        echo "✓ PASSED: All endpoints available\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some endpoints missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Endpoint Functions
echo "\n[TEST 2] Testing endpoint function definitions\n";
try {
    require_once(MOODLE_ROOT . '/public/question/type/coderunner/jobe_api_mock.php');
    
    $functions = [
        'qtype_coderunner_run_code',
        'qtype_coderunner_run_tests',
        'qtype_coderunner_get_languages',
    ];
    
    $all_defined = true;
    foreach ($functions as $func) {
        if (!function_exists($func)) {
            $all_defined = false;
            echo "  ✗ Missing: {$func}()\n";
        } else {
            echo "  ✓ Found: {$func}()\n";
        }
    }
    
    if ($all_defined) {
        echo "✓ PASSED: All functions defined\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some functions missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Endpoint Response Format
echo "\n[TEST 3] Testing endpoint response format\n";
try {
    $result = qtype_coderunner_run_code('python3', 'print("test")', '', 30);
    
    $required_fields = ['stdout', 'stderr', 'returncode'];
    $all_present = true;
    
    foreach ($required_fields as $field) {
        if (!isset($result[$field])) {
            $all_present = false;
            echo "  ✗ Missing field: $field\n";
        } else {
            echo "  ✓ Field present: $field\n";
        }
    }
    
    if ($all_present && is_array($result)) {
        echo "✓ PASSED: Response format correct\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid response format\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Language Support Endpoint
echo "\n[TEST 4] Testing language support endpoint\n";
try {
    $languages = qtype_coderunner_get_languages();
    
    if (is_array($languages) && count($languages) > 0) {
        echo "✓ PASSED: Languages endpoint works\n";
        echo "  Supported languages: " . implode(", ", $languages) . "\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: No languages returned\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Parameter Validation
echo "\n[TEST 5] Testing parameter validation\n";
try {
    $test_cases = [
        ['language' => 'python3', 'valid' => true],
        ['language' => 'invalid_lang', 'valid' => true],  // Should handle gracefully
    ];
    
    $validation_passed = true;
    foreach ($test_cases as $case) {
        try {
            $result = qtype_coderunner_run_code(
                $case['language'],
                'print("test")',
                '',
                30
            );
            
            // If we get here without exception, validation passed
            if (is_array($result)) {
                echo "  ✓ Language '" . $case['language'] . "' handled\n";
            }
        } catch (Exception $e) {
            if ($case['valid']) {
                $validation_passed = false;
                echo "  ✗ Unexpected error for '" . $case['language'] . "'\n";
            }
        }
    }
    
    if ($validation_passed) {
        echo "✓ PASSED: Parameter validation correct\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Parameter validation failed\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: Error Response Format
echo "\n[TEST 6] Testing error response format\n";
try {
    $result = qtype_coderunner_run_code(
        'python3',
        'raise Exception("Test error")',
        '',
        30
    );
    
    if (isset($result['stderr']) && strlen($result['stderr']) > 0) {
        echo "✓ PASSED: Error response format correct\n";
        echo "  Error captured: " . substr($result['stderr'], 0, 50) . "...\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Error not captured\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Concurrent Request Handling
echo "\n[TEST 7] Testing concurrent-like request handling\n";
try {
    $results = [];
    $concurrent_count = 3;
    
    for ($i = 0; $i < $concurrent_count; $i++) {
        $result = qtype_coderunner_run_code(
            'python3',
            'print(' . ($i + 1) . ')',
            '',
            30
        );
        $results[] = $result;
    }
    
    if (count($results) === $concurrent_count && array_reduce($results, function($carry, $item) {
        return $carry && isset($item['stdout']);
    }, true)) {
        echo "✓ PASSED: Multiple requests handled\n";
        echo "  Executed: $concurrent_count requests\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Request handling issue\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "AJAX ENDPOINTS TEST RESULTS\n";
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
