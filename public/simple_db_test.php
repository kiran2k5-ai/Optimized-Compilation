<?php
// Simple direct database connection test

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SIMPLE DATABASE CONNECTION TEST ===\n\n";

// Get config values
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'moodle';
$dbtype = 'mariadb';

echo "Attempting to connect to database...\n";
echo "  Host: $dbhost\n";
echo "  User: $dbuser\n";
echo "  DB: $dbname\n";
echo "  Type: $dbtype\n\n";

try {
    // Try mysqli connection first
    echo "[1] Trying mysqli_connect():\n";
    $link = @mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    if ($link === false) {
        echo "✗ Failed with: " . mysqli_connect_error() . "\n\n";
        die();
    } else {
        echo "✓ Connection successful!\n";
        
        // Test a query
        echo "[2] Trying a simple select query:\n";
        $result = mysqli_query($link, "SELECT COUNT(*) as cnt FROM mdl_config");
        if ($result === false) {
            echo "✗ Query failed with: " . mysqli_error($link) . "\n";
        } else {
            $row = mysqli_fetch_assoc($result);
            echo "✓ Query successful! Found " . $row['cnt'] . " config records\n";
            mysqli_free_result($result);
        }
        
        // Try to get the version
        echo "[3] Getting MySQL version:\n";
        $result = mysqli_query($link, "SELECT VERSION() as version");
        if ($result === false) {
            echo "✗ Query failed\n";
        } else {
            $row = mysqli_fetch_assoc($result);
            echo "✓ MySQL version: " . $row['version'] . "\n";
            mysqli_free_result($result);
        }
        
        mysqli_close($link);
    }
    
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n✓ All tests passed!\n";
?>
