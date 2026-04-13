<?php
/**
 * MYSQL DIRECT CONNECTION TEST
 * Tests MySQL connection WITHOUT loading Moodle config
 */

echo "\n";
echo "==============================================\n";
echo "DIRECT MYSQL CONNECTION TEST\n";
echo "==============================================\n\n";

echo "[CHECK 1] Testing MySQL connection...\n";

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'moodle';

try {
    // Try to connect to MySQL
    $mysqli = new mysqli($host, $user, $password);
    
    if ($mysqli->connect_error) {
        echo "  ✗ CONNECTION FAILED\n";
        echo "  Error: " . $mysqli->connect_error . "\n";
        echo "\n  ⚠️  MySQL is not running!\n";
        echo "\n  SOLUTION:\n";
        echo "  1. Open XAMPP Control Panel\n";
        echo "  2. Click 'Start' next to MySQL\n";
        echo "  3. Wait 2-3 seconds for it to start\n";
        echo "  4. Try again\n";
        exit(1);
    }
    
    echo "  ✓ Connected to MySQL server\n";
    echo "  Host: $host\n";
    echo "  User: $user\n";
    
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[CHECK 2] Testing database selection...\n";

try {
    // Check if database exists
    $result = $mysqli->select_db($database);
    
    if (!$result) {
        echo "  ✗ Database '$database' NOT FOUND\n";
        echo "  Error: " . $mysqli->error . "\n";
        echo "\n  Available databases:\n";
        
        $databases = $mysqli->query("SHOW DATABASES");
        while ($row = $databases->fetch_row()) {
            echo "    - " . $row[0] . "\n";
        }
        
        exit(1);
    }
    
    echo "  ✓ Database '$database' exists\n";
    
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[CHECK 3] Testing tables...\n";

try {
    $tables = ['user', 'quiz', 'quiz_attempts', 'quiz_slots', 'question'];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        
        if ($result && $result->num_rows > 0) {
            $count_result = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
            $count_row = $count_result->fetch_assoc();
            $count = $count_row['cnt'];
            
            echo "  ✓ Table '$table' exists (Records: $count)\n";
        } else {
            echo "  ⚠ Table '$table' not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
}

echo "\n[CHECK 4] Testing php-cli MySQL extension...\n";
if (extension_loaded('mysqli')) {
    echo "  ✓ MySQLi extension is loaded\n";
} else {
    echo "  ✗ MySQLi extension NOT loaded\n";
}

if (extension_loaded('pdo_mysql')) {
    echo "  ✓ PDO MySQL extension is loaded\n";
} else {
    echo "  ✗ PDO MySQL extension NOT loaded\n";
}

echo "\n==============================================\n";
echo "✓ TEST COMPLETE\n";
echo "==============================================\n";
echo "\nIf MySQL is not running, use XAMPP Control Panel to start it.\n\n";

$mysqli->close();
?>
