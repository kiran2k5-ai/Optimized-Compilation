<?php
/**
 * Moodle Database Connection Test
 * Run this directly in browser to test database access
 */

define('CLI_SCRIPT', false);
$moodle_root = dirname(__FILE__);

echo "<h1>Moodle Database Connection Test</h1>";
echo "<hr>";

echo "<h2>Step 1: Loading Config</h2>";

try {
    require_once($moodle_root . '/config.php');
    echo "<p style='color: green;'>✓ Config loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Config failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 2: Testing Database Connection</h2>";

try {
    global $DB;
    
    $count = $DB->count_records('user');
    echo "<p style='color: green;'>✓ Database connected!</p>";
    echo "<p>Total users: <strong>$count</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
    
    echo "<h3>Alternative: Direct MySQLi Connection</h3>";
    
    try {
        $m = new mysqli('localhost', 'root', '', 'moodle');
        if (!$m->connect_error) {
            echo "<p style='color: green;'>✓ Direct MySQLi connection works!</p>";
            $m->close();
        }
    } catch (Exception $e2) {
        echo "<p style='color: red;'>✗ Direct connection also failed: " . $e2->getMessage() . "</p>";
    }
}

echo "<h2>Step 3: Checking Tables</h2>";

try {
    $m = new mysqli('localhost', 'root', '', 'moodle');
    
    $tables = ['mdl_user', 'mdl_quiz', 'mdl_quiz_attempts'];
    
    foreach ($tables as $table) {
        $r = $m->query("SELECT 1 FROM `$table` LIMIT 1");
        if ($r !== false) {
            echo "<p style='color: green;'>✓ Table $table exists and is accessible</p>";
        } else {
            echo "<p style='color: red;'>✗ Table $table error: " . $m->error . "</p>";
        }
    }
    
    $m->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

?>
