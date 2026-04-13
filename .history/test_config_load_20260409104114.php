<?php
define('CLI_SCRIPT', true);

$moodle_root = dirname(__FILE__);

echo "[1] Loading config.php...\n";

try {
    require_once($moodle_root . '/config.php');
    echo "✓ Config loaded\n";
} catch (Throwable $e) {
    echo "✗ Config error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "[2] Checking database object...\n";

if (!isset($DB)) {
    echo "✗ Database object not created\n";
    exit(1);
}

echo "✓ Database object exists\n";
echo "Database type: " . get_class($DB) . "\n";

?>
