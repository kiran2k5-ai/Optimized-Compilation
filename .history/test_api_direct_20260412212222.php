<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->libdir . '/filelib.php';

echo "=== TESTING API DIRECTLY ===\n";

$url = 'http://127.0.0.1/jobe/index.php/restapi/runs';

$payload = json_encode([
    'run_spec' => [
        'language_id' => 'python3',
        'sourcecode' => 'print(4+5)',
        'input' => ''
    ]
]);

echo "URL: $url\n";
echo "Payload: $payload\n\n";

$curl = new curl();
$curl->setHeader(['Content-Type: application/json']);

$response = $curl->post($url, $payload);

echo "Raw response:\n";
var_dump($response);

echo "\nHTTP Code: " . $curl->info['http_code'] . "\n";
echo "HTTP Info:\n";
var_dump($curl->info);
?>
