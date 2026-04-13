<?php
/**
 * PYODIDE API - ENDPOINT TEST
 * Tests Pyodide initialization and AJAX endpoints
 * File: tests_scripts/api_tests/pyodide_api_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/enable_pyodide.php');

echo "Testing Pyodide API Endpoints...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Pyodide Version
echo "\n[TEST 1] Testing Pyodide version configuration\n";
try {
    $version = @constant('PYODIDE_VERSION');
    if ($version && strlen($version) > 0) {
        echo "✓ PASSED: Pyodide version defined\n";
        echo "  Version: $version\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Pyodide version not defined\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: CDN URL
echo "\n[TEST 2] Testing Pyodide CDN URL\n";
try {
    $cdn_url = @constant('PYODIDE_CDN_URL');
    if ($cdn_url && strpos($cdn_url, 'pyodide') !== false) {
        echo "✓ PASSED: CDN URL configured\n";
        echo "  URL: $cdn_url\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid CDN URL\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: Timeout Configuration
echo "\n[TEST 3] Testing timeout configuration\n";
try {
    $timeout = @constant('PYODIDE_TIMEOUT');
    if ($timeout && is_numeric($timeout) && $timeout > 0) {
        echo "✓ PASSED: Timeout configured\n";
        echo "  Timeout: {$timeout}s\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid timeout\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Max Output Size
echo "\n[TEST 4] Testing max output size\n";
try {
    $max_output = @constant('PYODIDE_MAX_OUTPUT');
    if ($max_output && is_numeric($max_output) && $max_output > 0) {
        echo "✓ PASSED: Max output size configured\n";
        echo "  Max size: {$max_output} bytes\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid max output\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: JavaScript File Check
echo "\n[TEST 5] Testing JavaScript executor file\n";
try {
    $js_file = MOODLE_ROOT . '/public/question/type/coderunner/pyodide_executor.js';
    if (file_exists($js_file)) {
        $size = filesize($js_file);
        echo "✓ PASSED: JavaScript executor exists\n";
        echo "  File: pyodide_executor.js\n";
        echo "  Size: " . number_format($size) . " bytes\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: JavaScript file not found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: API Mock File Check
echo "\n[TEST 6] Testing API mock file\n";
try {
    $api_file = MOODLE_ROOT . '/public/question/type/coderunner/jobe_api_mock.php';
    if (file_exists($api_file)) {
        $size = filesize($api_file);
        echo "✓ PASSED: API mock exists\n";
        echo "  File: jobe_api_mock.php\n";
        echo "  Size: " . number_format($size) . " bytes\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: API mock file not found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Configuration Validation
echo "\n[TEST 7] Testing enable_pyodide configuration\n";
try {
    $enable_file = MOODLE_ROOT . '/public/question/type/coderunner/enable_pyodide.php';
    if (file_exists($enable_file)) {
        $content = file_get_contents($enable_file);
        
        $checks = [
            'PYODIDE_VERSION' => strpos($content, 'PYODIDE_VERSION') !== false,
            'PYODIDE_CDN_URL' => strpos($content, 'PYODIDE_CDN_URL') !== false,
            'PYODIDE_TIMEOUT' => strpos($content, 'PYODIDE_TIMEOUT') !== false,
            'PYODIDE_MAX_OUTPUT' => strpos($content, 'PYODIDE_MAX_OUTPUT') !== false,
        ];
        
        $all_defined = array_reduce($checks, function($carry, $item) {
            return $carry && $item;
        }, true);
        
        if ($all_defined) {
            echo "✓ PASSED: All configurations defined\n";
            foreach ($checks as $name => $defined) {
                echo "  ✓ $name\n";
            }
            $tests_passed++;
        } else {
            echo "✗ FAILED: Some configurations missing\n";
            foreach ($checks as $name => $defined) {
                echo "  " . ($defined ? "✓" : "✗") . " $name\n";
            }
            $tests_failed++;
        }
    } else {
        echo "✗ FAILED: Configuration file not found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 8: AJAX Handler Check
echo "\n[TEST 8] Testing AJAX handler availability\n";
try {
    $handler_file = MOODLE_ROOT . '/public/question/type/coderunner/lib_integration.php';
    if (file_exists($handler_file)) {
        $content = file_get_contents($handler_file);
        
        $handlers = [
            'qtype_coderunner_handle_execution_request' => strpos($content, 'qtype_coderunner_handle_execution_request') !== false,
            'qtype_coderunner_get_pyodide_status' => strpos($content, 'qtype_coderunner_get_pyodide_status') !== false,
        ];
        
        $all_found = array_reduce($handlers, function($carry, $item) {
            return $carry && $item;
        }, true);
        
        if ($all_found) {
            echo "✓ PASSED: AJAX handlers implemented\n";
            foreach ($handlers as $name => $found) {
                echo "  ✓ $name()\n";
            }
            $tests_passed++;
        } else {
            echo "✗ FAILED: Some handlers missing\n";
            foreach ($handlers as $name => $found) {
                echo "  " . ($found ? "✓" : "✗") . " $name()\n";
            }
            $tests_failed++;
        }
    } else {
        echo "✗ FAILED: Integration file not found\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "PYODIDE API TEST RESULTS\n";
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
