<?php
define('CLI_SCRIPT', true);
require_once 'config.php';

echo "=== TESTING WITH 127.0.0.1 INSTEAD OF localhost ===\n";

// Test both URLs
$urls = [
    'http://localhost/jobe/index.php/restapi/languages',
    'http://127.0.0.1/jobe/index.php/restapi/languages',
];

foreach ($urls as $url) {
    echo "\nTesting: $url\n";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        if ($errno) {
            echo "  ❌ CURL error: $error\n";
        } else {
            echo "  HTTP Code: {$info['http_code']}\n";
            if ($info['http_code'] == 200) {
                echo "  ✅ SUCCESS\n";
            } else {
                echo "  Response: " . substr($response, 0, 100) . "\n";
            }
        }
    } catch (Exception $e) {
        echo "  Exception: " . $e->getMessage() . "\n";
    }
}
?>
