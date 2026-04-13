<?php
// Direct mysqli connection to check questions
$mysqli = new mysqli('localhost', 'root', '', 'moodle');

if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

echo "=== CHECKING ALL QUESTIONS ===\n\n";

$result = $mysqli->query("SELECT id, name, qtype FROM mdl_question ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, QType: {$row['qtype']}\n";
    }
} else {
    echo "Query Error: " . $mysqli->error . "\n";
}

echo "\n=== CHECKING FOR NULL OR EMPTY QTYPES ===\n";
$result = $mysqli->query("SELECT id, name, qtype FROM mdl_question WHERE qtype IS NULL OR qtype = ''");
if ($result && $result->num_rows > 0) {
    echo "Found questions with invalid qtype:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, QType: " . (empty($row['qtype']) ? '[EMPTY]' : $row['qtype']) . "\n";
    }
} else {
    echo "No questions with invalid qtype found.\n";
}

echo "\n=== DISTINCT QTYPES ===\n";
$result = $mysqli->query("SELECT DISTINCT qtype FROM mdl_question");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "QType: " . $row['qtype'] . "\n";
    }
}

echo "\n=== LOG ERRORS ===\n";
$result = $mysqli->query("SELECT * FROM mdl_log WHERE action LIKE '%error%' OR action LIKE '%exception%' ORDER BY time DESC LIMIT 10");
if ($result) {
    echo "Found " . $result->num_rows . " error log entries\n";
    while ($row = $result->fetch_assoc()) {
        echo "Time: {$row['time']}, Module: {$row['module']}, Action: {$row['action']}\n";
    }
}

$mysqli->close();
?>
