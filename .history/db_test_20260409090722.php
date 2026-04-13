<?php
/**
 * Moodle Database Connection Test
 * Access at: http://localhost/moodle/db_test.php
 */

echo "<h1>Moodle Database Connection Test</h1>";
echo "<hr>";

echo "<h2>[1] Loading Moodle Config</h2>";

define('CLI_SCRIPT', false);
$moodle_root = dirname(__FILE__);

try {
    require_once($moodle_root . '/config.php');
    echo "<p style='color: green;'><b>✓ Config loaded successfully</b></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><b>✗ Config failed:</b></p>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    // Try direct connection
    echo "<h2>[2] Trying Direct MySQLi Connection</h2>";
    
    try {
        $m = new mysqli('localhost', 'root', '', 'moodle');
        if ($m->connect_error) {
            echo "<p style='color: red;'>✗ MySQLi Connection Failed: " . $m->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>✓ Direct MySQLi Connection Works!</p>";
            $m->close();
        }
    } catch (Exception $e2) {
        echo "<p style='color: red;'>✗ Exception: " . $e2->getMessage() . "</p>";
    }
    
    exit;
}

echo "<h2>[2] Testing Moodle Database Object</h2>";

try {
    global $DB;
    
    $user_count = $DB->count_records('user');
    echo "<p style='color: green;'><b>✓ Database connected!</b></p>";
    echo "<p>Total users in system: <strong>$user_count</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><b>✗ Database error:</b></p>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<h2>[3] Checking Quiz Tables</h2>";

try {
    global $DB;
    
    $tables = [
        'quiz' => 'Quiz',
        'quiz_attempts' => 'Quiz Attempts',
        'quiz_slots' => 'Quiz Slots',
        'question' => 'Questions'
    ];
    
    foreach ($tables as $table => $label) {
        try {
            $count = $DB->count_records($table);
            echo "<p style='color: green;'>✓ $label ($table): <strong>$count records</strong></p>";
        } catch (Exception $te) {
            echo "<p style='color: red;'>✗ $label ($table): " . $te->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✓ Test Complete</h2>";
echo "<p>If all tests passed, your Moodle database is working correctly.</p>";

?>
