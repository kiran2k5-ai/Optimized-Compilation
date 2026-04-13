<?php
/**
 * DETAILED DATABASE DIAGNOSIS
 * Uses INFORMATION_SCHEMA to get accurate table info
 */

echo "\n";
echo "==============================================\n";
echo "DETAILED DATABASE DIAGNOSIS\n";
echo "==============================================\n\n";

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'moodle';

try {
    $mysqli = new mysqli($host, $user, $password);
    
    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error;
        exit(1);
    }
    
    echo "[STEP 1] Getting database info from INFORMATION_SCHEMA...\n\n";
    
    // Use INFORMATION_SCHEMA to get table info
    $query = "SELECT TABLE_NAME, TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database' ORDER BY TABLE_NAME";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        echo "Query failed: " . $mysqli->error . "\n";
        exit(1);
    }
    
    if ($result->num_rows == 0) {
        echo "❌ NO TABLES FOUND\n";
        echo "The moodle database appears to be empty.\n";
        exit(1);
    }
    
    echo "✓ Found " . $result->num_rows . " tables in '$database' database:\n\n";
    
    $table_number = 0;
    while ($row = $result->fetch_assoc()) {
        $table_number++;
        $table_name = $row['TABLE_NAME'];
        $table_rows = $row['TABLE_ROWS'] ?? 'N/A';
        
        echo "  " . str_pad($table_number, 3, " ", STR_PAD_LEFT) . ". $table_name";
        if ($table_rows !== 'N/A') {
            echo " (" . number_format($table_rows) . " rows)";
        }
        echo "\n";
    }
    
    echo "\n";
    
    // Check for key tables
    echo "[STEP 2] Checking for critical tables...\n\n";
    
    $critical_tables = [
        'mdl_user' => 'Users table',
        'mdl_quiz' => 'Quiz table',
        'mdl_quiz_attempts' => 'Quiz attempts',
        'mdl_question' => 'Questions',
        'mdl_question_attempts' => 'Question attempts',
        'mdl_quiz_slots' => 'Quiz slots'
    ];
    
    foreach ($critical_tables as $table => $description) {
        $check = $mysqli->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'");
        $row = $check->fetch_assoc();
        
        if ($row['cnt'] > 0) {
            echo "  ✓ $table ($description)\n";
        } else {
            echo "  ✗ $table ($description) - MISSING\n";
        }
    }
    
    echo "\n[STEP 3] Summary...\n\n";
    
    if ($result->num_rows > 50) {
        echo "✓ Database has " . $result->num_rows . " tables - Looks properly installed!\n";
    } else if ($result->num_rows > 0) {
        echo "⚠ Database has only " . $result->num_rows . " tables - May be incomplete installation\n";
    } else {
        echo "❌ Database is empty\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit(1);
}

?>
