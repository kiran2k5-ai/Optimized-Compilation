<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
global $DB;

echo "=== CHECKING JOBE CONFIGURATION ===\n";

$jobe_host = get_config('qtype_coderunner', 'jobe_host');
$jobe_port = get_config('qtype_coderunner', 'jobe_port');
$jobe_user = get_config('qtype_coderunner', 'jobe_user');
$jobe_apikey = get_config('qtype_coderunner', 'jobe_apikey');

echo "jobe_host: " . var_export($jobe_host, true) . "\n";
echo "jobe_port: " . var_export($jobe_port, true) . "\n";
echo "jobe_user: " . var_export($jobe_user, true) . "\n";
echo "jobe_apikey: " . var_export($jobe_apikey, true) . "\n";

// Get all CodeRunner settings
echo "\n=== ALL CODERUNNER SETTINGS ===\n";
$settings = $DB->get_records('config_plugins', ['plugin' => 'qtype_coderunner']);
foreach ($settings as $s) {
    echo "{$s->name} = " . var_export($s->value, true) . "\n";
}
?>
