<?php
/**
 * STANDALONE TEST RUNNER
 * Runs tests without loading full Moodle config
 * Uses direct MySQLi connection
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "============================================\n";
echo "  CODERUNNER + PYODIDE TEST RUNNER\n";
echo "  (Standalone - Direct Database Connection)\n";
echo "============================================\n\n";

// Direct MySQLi setup instead of Moodle config
$db = new mysqli('localhost', 'root', '', 'moodle');

if ($db->connect_error) {
    echo "✗ Database connection failed: " . $db->connect_error . "\n";
    exit(1);
}

echo "✓ Connected to Moodle database\n\n";

define('MOODLE_ROOT', dirname(dirname(__FILE__)));

$tests_passed = 0;
$tests_failed = 0;

// ============================================
// LOAD AND RUN TESTS
// ============================================

echo "Loading test suites...\n\n";

$test_dirs = [
    'api_tests' => MOODLE_ROOT . '/tests_scripts/api_tests',
    'function_tests' => MOODLE_ROOT . '/tests_scripts/function_tests',
    'integration_tests' => MOODLE_ROOT . '/tests_scripts/integration_tests',
];

foreach ($test_dirs as $type => $dir) {
    if (!is_dir($dir)) {
        echo "⚠ Test directory not found: $dir\n";
        continue;
    }
    
    $files = glob($dir . '/*_test.php');
    
    echo "===========================================\n";
    echo strtoupper(str_replace('_', ' ', $type)) . "\n";
    echo "===========================================\n\n";
    
    foreach ($files as $file) {
        $filename = basename($file);
        echo "Running: $filename\n";
        
        // Create an isolated environment for each test
        $output = shell_exec("e:\\moodel_xampp\\php\\php.exe -f \"$file\" 2>&1");
        
        // Count results
        if (stripos($output, 'PASSED') !== false) {
            $pass_count = substr_count($output, 'PASSED');
            $fail_count = substr_count($output, 'FAILED');
            
            $tests_passed += $pass_count;
            $tests_failed += $fail_count;
            
            echo "  ✓ $pass_count passed";
            if ($fail_count > 0) {
                echo ", ✗ $fail_count failed";
            }
            echo "\n\n";
        } else if (stripos($output, 'Error') !== false || stripos($output, 'FAILED') !== false) {
            echo "  ✗ Test execution failed\n";
            echo "  Error details: " . substr($output, 0, 200) . "...\n\n";
            $tests_failed++;
        }
    }
}

echo "\n";
echo "============================================\n";
echo "TEST SUMMARY\n";
echo "============================================\n";
echo "Total Passed: $tests_passed\n";
echo "Total Failed: $tests_failed\n";
echo "Pass Rate: " . ($tests_passed + $tests_failed > 0 ? round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 1) : 0) . "%\n";
echo "============================================\n\n";

$db->close();

if ($tests_failed == 0 && $tests_passed > 0) {
    echo "✓ ALL TESTS PASSED!\n\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED\n\n";
    exit(1);
}

?>
