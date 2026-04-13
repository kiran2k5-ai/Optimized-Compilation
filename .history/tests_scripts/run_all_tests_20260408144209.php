<?php
/**
 * MASTER TEST RUNNER
 * Runs all test suites and generates comprehensive reports
 * File: tests_scripts/run_all_tests.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Moodle context setup
define('CLI_SCRIPT', true);
define('TESTS_SCRIPTS_DIR', dirname(__FILE__));
define('MOODLE_ROOT', dirname(dirname(__FILE__)));

require_once(MOODLE_ROOT . '/config.php');

// Test statistics
$test_results = [
    'api_tests' => [],
    'function_tests' => [],
    'integration_tests' => [],
    'summary' => [
        'total_tests' => 0,
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0,
        'execution_time' => 0,
        'timestamp' => date('Y-m-d H:i:s'),
    ]
];

$start_time = microtime(true);

echo "\n";
echo "============================================\n";
echo "  PYODIDE INTEGRATION - MASTER TEST RUNNER\n";
echo "============================================\n\n";

// Color codes for CLI
define('COLOR_RESET', "\033[0m");
define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");

// ============================================
// API TESTS
// ============================================
echo COLOR_BLUE . "=== API ENDPOINT TESTS ===" . COLOR_RESET . "\n";

$api_test_files = [
    'api_tests/jobe_api_mock_test.php',
    'api_tests/pyodide_api_test.php',
    'api_tests/ajax_endpoints_test.php',
];

foreach ($api_test_files as $file) {
    $filepath = TESTS_SCRIPTS_DIR . '/' . $file;
    if (file_exists($filepath)) {
        echo "Running: $file\n";
        
        // Capture test output
        ob_start();
        include $filepath;
        $output = ob_get_clean();
        
        // Parse results
        if (strpos($output, 'FAILED') !== false) {
            echo COLOR_RED . "  ✗ FAILED" . COLOR_RESET . "\n";
            $test_results['summary']['failed']++;
        } else {
            echo COLOR_GREEN . "  ✓ PASSED" . COLOR_RESET . "\n";
            $test_results['summary']['passed']++;
        }
        $test_results['summary']['total_tests']++;
    }
}

echo "\n";

// ============================================
// FUNCTION TESTS
// ============================================
echo COLOR_BLUE . "=== FUNCTION TESTS ===" . COLOR_RESET . "\n";

$function_test_files = [
    'function_tests/enable_pyodide_test.php',
    'function_tests/lib_integration_test.php',
    'function_tests/execution_test.php',
    'function_tests/database_test.php',
];

foreach ($function_test_files as $file) {
    $filepath = TESTS_SCRIPTS_DIR . '/' . $file;
    if (file_exists($filepath)) {
        echo "Running: $file\n";
        
        ob_start();
        include $filepath;
        $output = ob_get_clean();
        
        if (strpos($output, 'FAILED') !== false) {
            echo COLOR_RED . "  ✗ FAILED" . COLOR_RESET . "\n";
            $test_results['summary']['failed']++;
        } else {
            echo COLOR_GREEN . "  ✓ PASSED" . COLOR_RESET . "\n";
            $test_results['summary']['passed']++;
        }
        $test_results['summary']['total_tests']++;
    }
}

echo "\n";

// ============================================
// INTEGRATION TESTS
// ============================================
echo COLOR_BLUE . "=== INTEGRATION TESTS ===" . COLOR_RESET . "\n";

$integration_test_files = [
    'integration_tests/full_workflow_test.php',
    'integration_tests/question_rendering_test.php',
    'integration_tests/attempt_handling_test.php',
];

foreach ($integration_test_files as $file) {
    $filepath = TESTS_SCRIPTS_DIR . '/' . $file;
    if (file_exists($filepath)) {
        echo "Running: $file\n";
        
        ob_start();
        include $filepath;
        $output = ob_get_clean();
        
        if (strpos($output, 'FAILED') !== false) {
            echo COLOR_RED . "  ✗ FAILED" . COLOR_RESET . "\n";
            $test_results['summary']['failed']++;
        } else {
            echo COLOR_GREEN . "  ✓ PASSED" . COLOR_RESET . "\n";
            $test_results['summary']['passed']++;
        }
        $test_results['summary']['total_tests']++;
    }
}

echo "\n";

// ============================================
// SUMMARY
// ============================================
$execution_time = microtime(true) - $start_time;
$test_results['summary']['execution_time'] = round($execution_time, 2);

echo COLOR_BLUE . "============================================================" . COLOR_RESET . "\n";
echo COLOR_BLUE . "  TEST SUMMARY" . COLOR_RESET . "\n";
echo COLOR_BLUE . "============================================================" . COLOR_RESET . "\n\n";

echo "Total Tests: " . $test_results['summary']['total_tests'] . "\n";
echo COLOR_GREEN . "Passed: " . $test_results['summary']['passed'] . COLOR_RESET . "\n";
echo COLOR_RED . "Failed: " . $test_results['summary']['failed'] . COLOR_RESET . "\n";
echo "Execution Time: " . $test_results['summary']['execution_time'] . " seconds\n";
echo "Timestamp: " . $test_results['summary']['timestamp'] . "\n\n";

// Calculate pass rate
$pass_rate = ($test_results['summary']['total_tests'] > 0) 
    ? round(($test_results['summary']['passed'] / $test_results['summary']['total_tests']) * 100, 2)
    : 0;

if ($pass_rate == 100) {
    echo COLOR_GREEN . "✓ ALL TESTS PASSED - System is ready!" . COLOR_RESET . "\n";
} elseif ($pass_rate >= 80) {
    echo COLOR_YELLOW . "⚠ Most tests passed - Review failures" . COLOR_RESET . "\n";
} else {
    echo COLOR_RED . "✗ CRITICAL FAILURES - Fix required!" . COLOR_RESET . "\n";
}

echo "Pass Rate: $pass_rate%\n\n";

// ============================================
// SAVE REPORTS
// ============================================
$reports_dir = TESTS_SCRIPTS_DIR . '/reports';
if (!is_dir($reports_dir)) {
    mkdir($reports_dir, 0755, true);
}

// JSON Report
file_put_contents(
    $reports_dir . '/test_results.json',
    json_encode($test_results, JSON_PRETTY_PRINT)
);

// Text Report
$text_report = "PYODIDE INTEGRATION - TEST REPORT\n";
$text_report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
$text_report .= str_repeat("=", 60) . "\n\n";
$text_report .= "SUMMARY:\n";
$text_report .= "  Total Tests: " . $test_results['summary']['total_tests'] . "\n";
$text_report .= "  Passed: " . $test_results['summary']['passed'] . "\n";
$text_report .= "  Failed: " . $test_results['summary']['failed'] . "\n";
$text_report .= "  Pass Rate: $pass_rate%\n";
$text_report .= "  Execution Time: " . $test_results['summary']['execution_time'] . " seconds\n\n";

file_put_contents(
    $reports_dir . '/test_report.txt',
    $text_report
);

// HTML Report
$html_report = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Pyodide Integration Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .header { border-bottom: 3px solid #0066cc; margin-bottom: 20px; }
        h1 { color: #0066cc; margin: 0; }
        .summary { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .metric { display: inline-block; margin-right: 30px; }
        .metric label { font-weight: bold; }
        .passed { color: green; font-weight: bold; }
        .failed { color: red; font-weight: bold; }
        .percentage { font-size: 24px; font-weight: bold; color: #0066cc; }
        .footer { margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pyodide Integration - Test Report</h1>
        </div>
        
        <div class="summary">
            <h2>Test Results Summary</h2>
            <div class="metric">
                <label>Total Tests:</label> {$test_results['summary']['total_tests']}
            </div>
            <div class="metric">
                <label>Passed:</label> <span class="passed">{$test_results['summary']['passed']}</span>
            </div>
            <div class="metric">
                <label>Failed:</label> <span class="failed">{$test_results['summary']['failed']}</span>
            </div>
            <br><br>
            <div class="metric">
                <label>Pass Rate:</label> <span class="percentage">$pass_rate%</span>
            </div>
            <div class="metric">
                <label>Execution Time:</label> {$test_results['summary']['execution_time']} seconds
            </div>
        </div>
        
        <div class="footer">
            <p>Generated: {$test_results['summary']['timestamp']}</p>
            <p>Status: <strong>
HTML;

$html_report .= ($pass_rate == 100) ? "✓ ALL TESTS PASSED" : (($pass_rate >= 80) ? "⚠ REVIEW NEEDED" : "✗ CRITICAL FAILURES");

$html_report .= <<<HTML
</strong></p>
        </div>
    </div>
</body>
</html>
HTML;

file_put_contents(
    $reports_dir . '/test_report.html',
    $html_report
);

echo COLOR_BLUE . "Reports saved to: tests_scripts/reports/" . COLOR_RESET . "\n";
echo "  ✓ test_results.json\n";
echo "  ✓ test_report.txt\n";
echo "  ✓ test_report.html\n";
echo "\n" . COLOR_BLUE . "============================================================" . COLOR_RESET . "\n";
echo "Tests complete! View reports in: tests_scripts/reports/\n\n";

exit($test_results['summary']['failed'] > 0 ? 1 : 0);
?>
