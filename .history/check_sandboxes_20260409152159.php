<?php
define('CLI_SCRIPT', true);
require_once 'config.php';
require_once $CFG->dirroot . '/question/type/coderunner/classes/sandbox.php';

echo "=== CHECKING AVAILABLE SANDBOXES ===\n";
$available = qtype_coderunner_sandbox::available_sandboxes();
var_dump($available);

echo "\n=== CHECKING ENABLED SANDBOXES ===\n";
$enabled = qtype_coderunner_sandbox::enabled_sandboxes();
var_dump($enabled);

echo "\n=== CHECKING CONFIG FOR EACH SANDBOX ===\n";
foreach (['jobesandbox', 'ideonesandbox'] as $sb) {
    $is_enabled = get_config('qtype_coderunner', $sb . '_enabled');
    echo "$sb" . "_enabled: " . var_export($is_enabled, true) . "\n";
}
?>
