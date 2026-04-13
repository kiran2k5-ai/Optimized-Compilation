<?php
/**
 * DIAGNOSTIC TEST RUNNER
 * Shows exactly what's happening with each test
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(__FILE__));

echo "\n==================================================\n";
echo "DIAGNOSTIC TEST RUNNER\n";
echo "==================================================\n\n";

require_once($moodle_root . '/config.php');

// ==============================================
// STEP 1: Verify jobe_api_mock.php
// ==============================================
echo "[STEP 1] Verifying jobe_api_mock.php\n";
echo "-------------------------------------------\n";

$jobe_file = $moodle_root . '/public/question/type/coderunner/jobe_api_mock.php';
echo "File path: $jobe_file\n";
echo "File exists: " . (file_exists($jobe_file) ? "YES" : "NO") . "\n";

if (file_exists($jobe_file)) {
    $content = file_get_contents($jobe_file);
    echo "File size: " . strlen($content) . " bytes\n";
    echo "Contains 'qtype_coderunner_get_languages': " . (strpos($content, 'qtype_coderunner_get_languages') !== false ? "YES" : "NO") . "\n";
    echo "Contains 'function': " . (strpos($content, 'function') !== false ? "YES" : "NO") . "\n";
    
    $lines = explode("\n", $content);
    echo "Total lines: " . count($lines) . "\n";
    
    // Show first 10 lines
    echo "\nFirst 10 lines of file:\n";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo "  Line " . ($i+1) . ": " . substr($lines[$i], 0, 60) . "\n";
    }
}

echo "\n";

// ==============================================
// STEP 2: Include the file
// ==============================================
echo "[STEP 2] Including jobe_api_mock.php\n";
echo "-------------------------------------------\n";

try {
    require_once($jobe_file);
    echo "✓ File included successfully\n";
} catch (Throwable $e) {
    echo "✗ Failed to include: " . $e->getMessage() . "\n\n";
    echo "Full error:\n";
    var_dump($e);
    exit(1);
}

echo "\n";

// ==============================================
// STEP 3: Check functions
// ==============================================
echo "[STEP 3] Checking functions\n";
echo "-------------------------------------------\n";

$functions = [
    'qtype_coderunner_get_languages',
    'qtype_coderunner_run_code',
    'qtype_coderunner_run_tests',
    'qtype_coderunner_get_jobe_server_url',
];

foreach ($functions as $func) {
    $exists = function_exists($func);
    echo ($exists ? "✓" : "✗") . " $func\n";
}

echo "\n";

// ==============================================
// STEP 4: Test function calls
// ==============================================
echo "[STEP 4] Testing function calls\n";
echo "-------------------------------------------\n";

// Test 1
echo "\n[TEST] qtype_coderunner_get_languages()\n";
try {
    $result = qtype_coderunner_get_languages();
    echo "Result: " . json_encode($result) . "\n";
    echo "Status: ✓ PASSED\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: ✗ FAILED\n";
}

// Test 2
echo "\n[TEST] qtype_coderunner_run_code()\n";
try {
    $result = qtype_coderunner_run_code('python3', 'print("test")', '', 30);
    echo "Keys: " . implode(', ', array_keys($result)) . "\n";
    echo "Has 'stdout': " . (isset($result['stdout']) ? "YES" : "NO") . "\n";
    echo "Has 'stderr': " . (isset($result['stderr']) ? "YES" : "NO") . "\n";
    echo "Has 'returncode': " . (isset($result['returncode']) ? "YES" : "NO") . "\n";
    echo "Status: ✓ PASSED\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: ✗ FAILED\n";
}

echo "\n";
echo "==================================================\n";
echo "DIAGNOSTIC COMPLETE\n";
echo "==================================================\n\n";
?>
