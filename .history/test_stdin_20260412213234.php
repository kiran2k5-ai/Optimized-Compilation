<?php
define('CLI_SCRIPT', true);
require_once 'config.php';

echo "=== TESTING MOCK JOBE API WITH STDIN ===\n\n";

// Test with stdin input
$test_code = <<<'PYTHON'
a = int(input())
b = int(input())
print(a+b)
PYTHON;

$ch = curl_init('http://127.0.0.1/jobe/index.php/restapi/runs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => $test_code,
        'stdin' => "4\n5"
    ]
]));

$resp = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($resp, true);

echo "Test: a = int(input()); b = int(input()); print(a+b)\n";
echo "Input: 4\\n5\n";
echo "Expected Output: 9\n\n";

echo "HTTP Status: $httpcode\n";
echo "Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

if ($result['outcome'] == 15) {
    echo "✅ SUCCESS! Input was properly passed to Python\n";
    echo "Output: " . trim($result['stdout']) . "\n";
} else {
    echo "❌ FAILED with outcome " . $result['outcome'] . "\n";
    echo "Error: " . $result['stderr'] . "\n";
}
?>
