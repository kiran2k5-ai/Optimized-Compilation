<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $CFG;

echo "=== CHECKING MOODLE CURL/SECURITY SETTINGS ===\n";

// Check curl settings
echo "curl_use_ssl: " . ($CFG->curl_use_ssl ?? 'not set') . "\n";

// Check if there are any URL restrictions
$allowed_urls = $CFG->noreplyaddress ?? '';
echo "noreplyaddress: " . var_export($allowed_urls, true) . "\n";

// Check if there's a URL whitelist/blacklist
$settings = $CFG;
echo "\nAll CFG properties related to curl/url:\n";
foreach ((array)$CFG as $key => $value) {
    if (stripos($key, 'curl') !== false || stripos($key, 'url') !== false || stripos($key, 'http') !== false) {
        echo "  \$CFG->$key = " . var_export($value, true) . "\n";
    }
}
?>
