<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== CodeRunner Configuration ===\n";
echo "jobe_host: " . get_config('qtype_coderunner', 'jobe_host') . "\n";
echo "jobe_port: " . get_config('qtype_coderunner', 'jobe_port') . "\n";
echo "jobe_sandbox_class: " . get_config('qtype_coderunner', 'jobe_sandbox_class') . "\n";
echo "jobe_apikey: " . get_config('qtype_coderunner', 'jobe_apikey') . "\n";
?>
