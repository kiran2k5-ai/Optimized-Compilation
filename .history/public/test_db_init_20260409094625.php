<?php
// Debug script to trace database initialization

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== MOODLE DATABASE INITIALIZATION DEBUG ===\n\n";

// Step 1: Check if config exists
echo "[1] Checking config file...\n";
if (file_exists('./config.php')) {
    echo "✓ config.php found\n";
} elseif (file_exists('../config.php')) {
    echo "✓ ../config.php found (trying to include)\n";
    require_once('../config.php');
} else {
    die("✗ config.php not found!");
}

// Step 2: Check if config was included
echo "\n[2] Checking if \$CFG is set...\n";
if (isset($CFG)) {
    echo "✓ \$CFG is set\n";
    echo "  dbtype: {$CFG->dbtype}\n";
    echo "  dbhost: {$CFG->dbhost}\n";
    echo "  dbname: {$CFG->dbname}\n";
    echo "  dbuser: {$CFG->dbuser}\n";
} else {
    die("✗ \$CFG not set!");
}

// Step 3: Try direct database connection
echo "\n[3] Testing direct MySQLi connection...\n";
try {
    $mysqli = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
    if ($mysqli->connect_error) {
        die("✗ Connection failed: " . $mysqli->connect_error);
    }
    echo "✓ Connected to {$CFG->dbname} successfully\n";
    
    // Test a simple query
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '{$CFG->dbname}'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Database has {$row['cnt']} tables\n";
    }
    $mysqli->close();
} catch (Exception $e) {
    die("✗ Direct connection failed: " . $e->getMessage());
}

// Step 4: Now try loading Moodle's setup
echo "\n[4] Loading Moodle setup...\n";
try {
    // Try to require the setup file
    require_once('../lib/setup.php');
    echo "✓ lib/setup.php loaded successfully\n";
} catch (Exception $e) {
    die("✗ lib/setup.php failed: " . $e->getMessage());
}

echo "\n✓ All initialization steps passed!\n";
?>
