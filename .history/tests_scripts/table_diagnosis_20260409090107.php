<?php
/**
 * DATABASE TABLE DIAGNOSIS
 * Checks table engines and status
 */

echo "\n";
echo "==============================================\n";
echo "DATABASE TABLE DIAGNOSIS\n";
echo "==============================================\n\n";

$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit(1);
}

echo "[1] Checking table details with information_schema...\n\n";

$query = <<<SQL
SELECT 
  TABLE_NAME,
  ENGINE,
  TABLE_TYPE,
  TABLE_ROWS,
  DATA_FREE,
  TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'moodle'
AND TABLE_NAME IN ('mdl_quiz', 'mdl_quiz_attempts', 'mdl_quiz_slots', 'mdl_question')
ORDER BY TABLE_NAME
SQL;

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Table: " . $row['TABLE_NAME'] . "\n";
        echo "  Engine: " . $row['ENGINE'] . "\n";
        echo "  Type: " . $row['TABLE_TYPE'] . "\n";
        echo "  Rows: " . ($row['TABLE_ROWS'] ?? 'N/A') . "\n";
        echo "  Data_free: " . ($row['DATA_FREE'] ?? 'N/A') . "\n";
        echo "  Comment: " . ($row['TABLE_COMMENT'] ?? 'None') . "\n";
        echo "\n";
    }
} else {
    echo "No tables found!\n";
}

echo "[2] Trying to use CHECK TABLE...\n\n";

$tables = ['mdl_quiz', 'mdl_quiz_attempts', 'mdl_quiz_slots', 'mdl_question'];

foreach ($tables as $table) {
    $check = $mysqli->query("CHECK TABLE `$table`");
    
    if ($check) {
        while ($row = $check->fetch_assoc()) {
            echo "Check result for '$table':\n";
            print_r($row);
            echo "\n";
        }
    } else {
        echo "Check failed for '$table': " . $mysqli->error . "\n\n";
    }
}

echo "[3] Trying REPAIR TABLE...\n\n";

foreach ($tables as $table) {
    $repair = $mysqli->query("REPAIR TABLE `$table`");
    
    if ($repair) {
        while ($row = $repair->fetch_assoc()) {
            echo "Repair result for '$table':\n";
            print_r($row);
            echo "\n";
        }
    } else {
        echo "Repair failed for '$table': " . $mysqli->error . "\n\n";
    }
}

echo "[4] Trying simple SELECT after repair...\n\n";

foreach ($tables as $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM `$table`");
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ $table: " . $row['cnt'] . " rows\n";
    } else {
        echo "✗ $table: " . $mysqli->error . "\n";
    }
}

$mysqli->close();

?>
