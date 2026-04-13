<?php
// Check what tables exist in moodle database
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

echo "=== CHECKING FOR CODERUNNER TABLES ===\n\n";

$result = $mysqli->query("SHOW TABLES LIKE 'mdl_question\_coderunner%'");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found CodeRunner tables:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - " . array_values($row)[0] . "\n";
        }
    } else {
        echo "NO CodeRunner tables found!\n";
    }
}

echo "\n=== ALL TABLES IN DATABASE ===\n";
$result = $mysqli->query("SHOW TABLES");
$tables = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tableName = array_values($row)[0];
        $tables[] = $tableName;
    }
    echo "Total tables: " . count($tables) . "\n";
    
    // Show only coderunner-related
    echo "\nCodeRunner mentions:\n";
    foreach ($tables as $t) {
        if (stripos($t, 'coderunner') !== false) {
            echo "  - $t\n";
        }
    }
}

$mysqli->close();
?>
