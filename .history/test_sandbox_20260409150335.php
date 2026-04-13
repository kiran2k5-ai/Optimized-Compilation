<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Testing Sandbox Loading ===\n\n";

require_once('public/question/type/coderunner/classes/sandbox.php');

echo "Enabled sandboxes:\n";
$enabled = qtype_coderunner_sandbox::enabled_sandboxes();
foreach ($enabled as $name => $class) {
    echo "  - $name => $class\n";
}

echo "\nTrying to get 'jobesandbox' instance...\n";
try {
    $sb = qtype_coderunner_sandbox::get_instance('jobesandbox');
    echo "✓ Success! Got sandbox instance: " . get_class($sb) . "\n";
    
    echo "\nTrying get_best_sandbox for python3...\n";
    $best = qtype_coderunner_sandbox::get_best_sandbox('python3');
    echo "✓ Got best sandbox: " . get_class($best) . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
