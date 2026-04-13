<?php
// Test if our mock API endpoint is reachable
$url = 'http://localhost/jobe/index.php/restapi/languages';

echo "=== TESTING MOCK API ENDPOINT ===\n";
echo "URL: $url\n";
echo "\n";

// Try with curl
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

echo "Making request...\n";
$response = curl_exec($curl);
$info = curl_getinfo($curl);
$errno = curl_errno($curl);
$error = curl_error($curl);
curl_close($curl);

if ($errno) {
    echo "❌ CURL ERROR: ($errno) $error\n";
} else {
    echo "HTTP Code: {$info['http_code']}\n";
    if ($info['http_code'] == 200) {
        echo "✅ Request successful!\n";
        echo "Response:\n";
        var_dump(json_decode($response, true));
    } else {
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
}
?>
