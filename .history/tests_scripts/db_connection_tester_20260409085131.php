<?php
/**
 * DATABASE CONNECTION TESTER
 * Diagnoses database connection issues
 */

define('CLI_SCRIPT', true);
$moodle_root = dirname(dirname(__FILE__));

echo "\n";
echo "==============================================\n";
echo "DATABASE CONNECTION TEST\n";
echo "==============================================\n\n";

echo "[STEP 1] Loading Moodle configuration...\n";
try {
    $config_file = $moodle_root . '/config.php';
    echo "  Looking for: $config_file\n";
    
    if (!file_exists($config_file)) {
        echo "  ✗ config.php NOT FOUND\n";
        exit(1);
    }
    
    echo "  ✓ config.php found\n";
    require_once($config_file);
    echo "  ✓ config.php loaded\n";
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[STEP 2] Checking database configuration...\n";
try {
    global $CFG;
    
    echo "  Database Host: " . $CFG->dbhost . "\n";
    echo "  Database Name: " . $CFG->dbname . "\n";
    echo "  Database User: " . $CFG->dbuser . "\n";
    echo "  Database Type: " . $CFG->dbtype . "\n";
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
}

echo "\n[STEP 3] Testing database connection...\n";
try {
    global $DB;
    
    // Test with a simple query
    $result = $DB->count_records('user');
    echo "  ✓ Database connected successfully\n";
    echo "  Total users in system: $result\n";
} catch (Exception $e) {
    echo "  ✗ connection FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\n  Troubleshooting steps:\n";
    echo "  1. Check if MySQL is running in XAMPP\n";
    echo "  2. Check XAMPP MySQL Port (usually 3306)\n";
    echo "  3. Check MySQL username in config.php\n";
    echo "  4. Check MySQL password in config.php\n";
    echo "  5. Verify database exists\n";
    exit(1);
}

echo "\n[STEP 4] Checking required tables...\n";
try {
    global $DB;
    $manager = $DB->get_manager();
    
    $required_tables = [
        'quiz',
        'quiz_attempts',
        'quiz_slots',
        'question',
        'question_attempts',
        'user'
    ];
    
    foreach ($required_tables as $table) {
        if ($manager->table_exists($table)) {
            $count = $DB->count_records($table);
            echo "  ✓ Table '$table' exists (Records: $count)\n";
        } else {
            echo "  ✗ Table '$table' NOT FOUND\n";
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n[STEP 5] Testing quiz_slots field names...\n";
try {
    global $DB;
    
    $slots = $DB->get_records('quiz_slots', [], '', '*', 0, 1);
    
    if (count($slots) > 0) {
        $slot = reset($slots);
        $fields = array_keys(get_object_vars($slot));
        
        echo "  Quiz_slots fields: " . implode(', ', $fields) . "\n";
        echo "\n  Expected fields:\n";
        echo "    - id\n";
        echo "    - quizid (or quiz)\n";
        echo "    - page\n";
        echo "    - slot\n";
        echo "    - question (or questionid)\n";
    } else {
        echo "  ⚠ No quiz_slots records found (database empty)\n";
        echo "  This is normal for a fresh Moodle install\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n==============================================\n";
echo "✓ DIAGNOSIS COMPLETE\n";
echo "==============================================\n\n";
?>
