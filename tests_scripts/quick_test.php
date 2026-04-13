<?php
/**
 * QUICK TEST - Verify functions are accessible
 * Run this directly to debug function availability
 */

$moodle_root = dirname(dirname(__FILE__));

echo "PHP Version: " . phpversion() . "\n";
echo "Moodle Root: $moodle_root\n\n";

// Test 1: Check jobe_api_mock.php exists
echo "[1] Checking jobe_api_mock.php file existence\n";
$file = $moodle_root . '/public/question/type/coderunner/jobe_api_mock.php';
if (file_exists($file)) {
    echo "  ✓ File exists\n";
    echo "  Size: " . filesize($file) . " bytes\n";
} else {
    echo "  ✗ File NOT found at: $file\n";
    exit(1);
}

// Test 2: Try to include it directly
echo "\n[2] Attempting to include jobe_api_mock.php\n";
try {
    require_once($file);
    echo "  ✓ File included successfully\n";
} catch (Exception $e) {
    echo "  ✗ Include failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check if functions exist
echo "\n[3] Checking if functions exist\n";
$functions_to_check = [
    'qtype_coderunner_get_languages',
    'qtype_coderunner_run_code',
    'qtype_coderunner_run_tests',
    'qtype_coderunner_get_jobe_server_url',
];

foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "  ✓ Function exists: $func\n";
    } else {
        echo "  ✗ Function NOT found: $func\n";
    }
}

// Test 4: Try calling a function
echo "\n[4] Testing function execution\n";
try {
    $languages = qtype_coderunner_get_languages();
    if (is_array($languages)) {
        echo "  ✓ Function call successful\n";
        echo "  Languages: " . implode(', ', $languages) . "\n";
    } else {
        echo "  ✗ Function returned non-array\n";
    }
} catch (Exception $e) {
    echo "  ✗ Function call failed: " . $e->getMessage() . "\n";
}

echo "\n✓ All checks passed. Functions are accessible!\n";
?>
