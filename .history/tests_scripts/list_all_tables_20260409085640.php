<?php
/**
 * LIST ALL TABLES IN MOODLE DATABASE
 * Shows what's actually in your moodle database
 */

echo "\n";
echo "==============================================\n";
echo "CHECKING ALL TABLES IN MOODLE DATABASE\n";
echo "==============================================\n\n";

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'moodle';

try {
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error;
        exit(1);
    }
    
    echo "Connected to database: $database\n\n";
    
    // Get all tables
    $result = $mysqli->query("SHOW TABLES");
    
    if ($result->num_rows == 0) {
        echo "❌ NO TABLES FOUND IN DATABASE\n";
        echo "The moodle database is empty!\n\n";
        echo "You need to run Moodle installation.\n";
        exit(1);
    }
    
    echo "📋 TABLES IN MOODLE DATABASE:\n\n";
    
    $table_count = 0;
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        $table_count++;
        
        // Count records in table
        $count_result = $mysqli->query("SELECT COUNT(*) as cnt FROM `$table`");
        $count_row = $count_result->fetch_assoc();
        $count = $count_row['cnt'];
        
        echo "  " . $table_count . ". $table (" . number_format($count) . " records)\n";
    }
    
    echo "\n✓ Total tables: $table_count\n";
    
    if ($table_count > 0) {
        echo "\n✓ Database appears to be properly installed!\n";
    } else {
        echo "\n❌ Database is empty - needs installation\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit(1);
}

?>
