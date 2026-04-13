<?php
define('CLI_SCRIPT', true);
require_once 'config.php';

echo "=== UPDATING JOBE CONFIGURATION ===\n";

// Change from 'localhost' to '127.0.0.1'
set_config('jobe_host', '127.0.0.1', 'qtype_coderunner');

purge_all_caches();

echo "✅ Updated jobe_host from 'localhost' to '127.0.0.1'\n";
echo "   This might resolve the 'URL is blocked' issue\n";

// Verify
$host = get_config('qtype_coderunner', 'jobe_host');
echo "\nVerification:\n";
echo "  jobe_host is now: '$host'\n";
?>
