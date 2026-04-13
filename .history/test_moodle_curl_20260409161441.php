<?php
define('CLI_SCRIPT', true);
require_once 'config.php';

echo "=== TESTING MOCK API WITH MOODLE CURL ===\n";

$url = 'http://localhost/jobe/index.php/restapi/languages';
echo "URL: $url\n";
echo "\n";

$curl = new curl();
$curl->setHeader(['User-Agent: CodeRunner', 'Accept: application/json']);

echo "Making request with Moodle curl class...\n";
$response = $curl->get($url);

echo "HTTP Code: " . $curl->info['http_code'] . "\n";
echo "Response type: " . gettype($response) . "\n";
echo "Response length: " . strlen($response) . "\n";

if ($response !== false) {
    echo "\nResponse:\n";
    $decoded = json_decode($response, true);
    var_dump($decoded);
} else {
    echo "Request failed!\n";
}
?>
