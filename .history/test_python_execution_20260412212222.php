<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->libdir . '/filelib.php';

echo "=== TESTING PYTHON CODE EXECUTION IN MOCK API ===\n";

$url = 'http://127.0.0.1/jobe/index.php/restapi/runs';

// Test 1: Simple print
$test1 = [
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => 'print(4+5)',
        'input' => ''
    ]
];

$curl = new curl();
$curl->setHeader(['Content-Type: application/json']);

echo "Test 1: Simple calculation\n";
echo "Code: print(4+5)\n";
$response = $curl->post($url, json_encode($test1));
$result = json_decode($response, true);
echo "Full response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
echo "Output: '" . ($result['stdout'] ?? 'none') . "'\n";
echo "Expected: '9'\n";
echo ($result['stdout'] == '9' ? "✅ PASS" : "❌ FAIL") . "\n\n";

// Test 2: With input
$test2 = [
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => "a = int(input())\nb = int(input())\nprint(a+b)",
        'input' => "4\n5"
    ]
];

echo "Test 2: With input\n";
echo "Code: a = int(input()); b = int(input()); print(a+b)\n";
echo "Input: 4, 5\n";
$response = $curl->post($url, json_encode($test2));
$result = json_decode($response, true);
echo "Output: '" . ($result['stdout'] ?? 'none') . "'\n";
echo "Expected: '9'\n";
echo ($result['stdout'] == '9' ? "✅ PASS" : "❌ FAIL") . "\n";
?>
