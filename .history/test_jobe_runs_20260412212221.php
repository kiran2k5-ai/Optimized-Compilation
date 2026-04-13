<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->libdir . '/filelib.php';

echo "=== TESTING UPDATED JOBE MOCK API ===\n";

$url = 'http://127.0.0.1/jobe/index.php/restapi/runs';

// Prepare payload like CodeRunner would send
$runspec = [
    'language_id' => 'python3',
    'sourcecode' => 'print(4+5)',
    'sourcefilename' => '__tester__.python3',
    'input' => '',
    'file_list' => []
];

$payload = ['run_spec' => $runspec];

$curl = new curl();
$curl->setHeader(['Content-Type: application/json', 'Accept: application/json']);

echo "Posting job to: $url\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

$response = $curl->post($url, json_encode($payload));
$httpcode = $curl->info['http_code'];

echo "HTTP Code: $httpcode\n";
echo "Response:\n";

if ($response) {
    $decoded = json_decode($response, true);
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    
    // Check if response has the required fields
    echo "\nChecking response fields:\n";
    foreach (['outcome', 'stdout', 'cmpinfo'] as $field) {
        if (isset($decoded[$field])) {
            echo "  ✅ $field: " . var_export($decoded[$field], true) . "\n";
        } else {
            echo "  ❌ MISSING: $field\n";
        }
    }
} else {
    echo "No response!\n";
}
?>
