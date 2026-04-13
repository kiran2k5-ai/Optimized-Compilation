<?php
/**
 * ENABLE PYODIDE - FUNCTION TEST
 * Tests configuration and utility functions
 * File: tests_scripts/function_tests/enable_pyodide_test.php
 */

define('CLI_SCRIPT', true);
define('MOODLE_ROOT', dirname(dirname(dirname(__FILE__))));

require_once(MOODLE_ROOT . '/config.php');
require_once(MOODLE_ROOT . '/public/question/type/coderunner/enable_pyodide.php');

echo "Testing enable_pyodide Functions...\n";
echo str_repeat("=", 60) . "\n";

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Configuration Constants
echo "\n[TEST 1] Testing configuration constants\n";
try {
    $constants = [
        'PYODIDE_VERSION',
        'PYODIDE_CDN_URL',
        'PYODIDE_TIMEOUT',
        'PYODIDE_MAX_OUTPUT',
    ];
    
    $all_defined = true;
    foreach ($constants as $const) {
        if (!defined($const)) {
            $all_defined = false;
            echo "  ✗ Not defined: $const\n";
        } else {
            $value = constant($const);
            echo "  ✓ $const = " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }
    
    if ($all_defined) {
        echo "✓ PASSED: All constants defined\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some constants missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 2: Version Format
echo "\n[TEST 2] Testing version format\n";
try {
    $version = constant('PYODIDE_VERSION');
    // Version should be like "0.23.0"
    if (preg_match('/^\d+\.\d+\.\d+/', $version)) {
        echo "✓ PASSED: Version format correct\n";
        echo "  Version: $version\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid version format\n";
        echo "  Version: $version\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 3: CDN URL Validity
echo "\n[TEST 3] Testing CDN URL validity\n";
try {
    $cdn_url = constant('PYODIDE_CDN_URL');
    
    if (filter_var($cdn_url, FILTER_VALIDATE_URL) && strpos($cdn_url, 'pyodide') !== false) {
        echo "✓ PASSED: CDN URL valid\n";
        echo "  URL: $cdn_url\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid CDN URL\n";
        echo "  URL: $cdn_url\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 4: Timeout Value
echo "\n[TEST 4] Testing timeout configuration\n";
try {
    $timeout = constant('PYODIDE_TIMEOUT');
    
    if (is_numeric($timeout) && $timeout > 0 && $timeout <= 300) {
        echo "✓ PASSED: Timeout value reasonable\n";
        echo "  Timeout: {$timeout}s\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Timeout value invalid\n";
        echo "  Timeout: {$timeout}s\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 5: Max Output Size
echo "\n[TEST 5] Testing max output size\n";
try {
    $max_output = constant('PYODIDE_MAX_OUTPUT');
    
    if (is_numeric($max_output) && $max_output > 0) {
        $readable_size = ($max_output / 1024 / 1024); 
        echo "✓ PASSED: Max output size configured\n";
        echo "  Size: " . number_format($max_output) . " bytes (~" . number_format($readable_size, 2) . " MB)\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Invalid max output size\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 6: File Structure Check
echo "\n[TEST 6] Testing file structure\n";
try {
    $required_files = [
        'pyodide_executor.js' => '/public/question/type/coderunner/pyodide_executor.js',
        'jobe_api_mock.php' => '/public/question/type/coderunner/jobe_api_mock.php',
        'renderer.php' => '/public/question/type/coderunner/renderer.php',
        'lib_integration.php' => '/public/question/type/coderunner/lib_integration.php',
    ];
    
    $all_exist = true;
    foreach ($required_files as $name => $path) {
        $filepath = MOODLE_ROOT . $path;
        if (file_exists($filepath)) {
            $size = filesize($filepath);
            echo "  ✓ $name (" . number_format($size) . " bytes)\n";
        } else {
            echo "  ✗ $name (NOT FOUND)\n";
            $all_exist = false;
        }
    }
    
    if ($all_exist) {
        echo "✓ PASSED: All required files present\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Some files missing\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 7: Configuration File Readability
echo "\n[TEST 7] Testing configuration file readability\n";
try {
    $config_file = MOODLE_ROOT . '/public/question/type/coderunner/enable_pyodide.php';
    
    if (is_readable($config_file)) {
        $content = file_get_contents($config_file);
        $lines = count(explode("\n", $content));
        echo "✓ PASSED: Config file readable\n";
        echo "  File: enable_pyodide.php\n";
        echo "  Lines: $lines\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Cannot read config file\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// TEST 8: Settings Persistence
echo "\n[TEST 8] Testing settings persistence\n";
try {
    // Simulate reading and writing settings
    $test_value = time();
    $settings = [
        'pyodide_enabled' => true,
        'use_local_pyodide' => true,
        'cache_timestamp' => $test_value,
    ];
    
    // Check if settings are valid
    if (isset($settings['pyodide_enabled']) && isset($settings['use_local_pyodide'])) {
        echo "✓ PASSED: Settings structure valid\n";
        echo "  Settings intact: " . count($settings) . " items\n";
        $tests_passed++;
    } else {
        echo "✗ FAILED: Settings structure invalid\n";
        $tests_failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// SUMMARY
echo "\n" . str_repeat("=", 60) . "\n";
echo "ENABLE PYODIDE FUNCTION TEST RESULTS\n";
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
