<?php
echo "=== CHECKING PYTHON AVAILABILITY ===\n";

// Check if Python is available
$output = shell_exec('python --version 2>&1');
if ($output) {
    echo "✅ Python found: " . trim($output) . "\n";
} else {
    echo "❌ Python not found via shell_exec\n";
}

// Try direct execution
echo "\n=== TESTING DIRECT SHELL EXECUTION ===\n";
$test = shell_exec('echo test 2>&1');
echo "Echo test result: '" . trim($test) . "'\n";

// Check if shell_exec is enabled
$disabled = ini_get('disable_functions');
echo "\nDisabled functions: " . ($disabled ?: 'none') . "\n";

// Try with full path
echo "\n=== TRYING WITH XAMPP PYTHON PATH ===\n";
$paths = [
    'python',
    'python3',
    'C:\\Python39\\python.exe',
    'E:\\moodel_xampp\\python\\python.exe',
];

foreach ($paths as $python_path) {
    $out = shell_exec("\"$python_path\" --version 2>&1");
    if ($out) {
        echo "✅ Found at: $python_path - " . trim($out) . "\n";
    }
}
?>
