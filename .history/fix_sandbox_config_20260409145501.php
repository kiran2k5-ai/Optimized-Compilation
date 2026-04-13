<?php
define('CLI_SCRIPT', true);
require 'config.php';

echo "=== Checking Sandbox Configuration ===\n\n";

// Check if jobesandbox is enabled
$jobesandbox_enabled = get_config('qtype_coderunner', 'jobesandbox_enabled');
echo "jobesandbox_enabled: " . ($jobesandbox_enabled ? 'YES' : 'NO') . "\n";

// Check what sandbox class is set
$sandbox_class = get_config('qtype_coderunner', 'jobe_sandbox_class');
echo "jobe_sandbox_class: " . $sandbox_class . "\n\n";

// Try to get the sandbox
$sandboxes = ['jobesandbox' => 'qtype_coderunner_jobesandbox'];
foreach ($sandboxes as $extname => $classname) {
    $is_enabled = get_config('qtype_coderunner', $extname . '_enabled');
    echo "Sandbox: $extname\n";
    echo "  - Class: $classname\n";
    echo "  - Enabled: " . ($is_enabled ? 'YES' : 'NO') . "\n";
}

echo "\n=== Fixing Configuration ===\n";

// Enable jobesandbox
set_config('jobesandbox_enabled', 1, 'qtype_coderunner');
echo "✓ Enabled jobesandbox\n";

// Set the correct sandbox name (not class name)
set_config('jobe_sandbox_class', 'jobesandbox', 'qtype_coderunner');
echo "✓ Set jobe_sandbox_class to 'jobesandbox' (external name)\n";

echo "\nConfiguration fixed!\n";
?>
