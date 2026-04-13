<?php
/**
 * LIST ACTUAL TABLE NAMES IN MOODLE DATABASE
 * Shows exact table names
 */

$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    echo "Connection failed\n";
    exit(1);
}

echo "EXACT TABLE NAMES IN MOODLE DATABASE:\n";
echo "=====================================\n\n";

// Get all tables using SHOW TABLES
$result = $mysqli->query("SHOW TABLES FROM moodle");

if ($result && $result->num_rows > 0) {
    $tables = [];
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    // Sort and display
    sort($tables);
    
    foreach ($tables as $i => $table) {
        echo ($i + 1) . ". " . $table . "\n";
    }
    
    echo "\n\nTables related to QUIZ:\n";
    foreach ($tables as $table) {
        if (stripos($table, 'quiz') !== false) {
            echo "  → " . $table . "\n";
        }
    }
    
    echo "\nTables related to QUESTION:\n";
    foreach ($tables as $table) {
        if (stripos($table, 'question') !== false) {
            echo "  → " . $table . "\n";
        }
    }
    
    echo "\nTables related to ATTEMPT:\n";
    foreach ($tables as $table) {
        if (stripos($table, 'attempt') !== false) {
            echo "  → " . $table . "\n";
        }
    }
} else {
    echo "No tables found\n";
}

$mysqli->close();

?>
